<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\PriceTypeTransfer;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProduct;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore;
use Propel\Runtime\Collection\ObjectCollection;

class PriceProductMapper
{
    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    public function mapPriceProductStoreEntityToPriceProductTransfer(
        SpyPriceProductStore $priceProductStoreEntity,
        PriceProductTransfer $priceProductTransfer
    ): PriceProductTransfer {
        $priceProductEntity = $priceProductStoreEntity->getPriceProduct();
        $priceProductStoreEntityData = $priceProductStoreEntity->toArray();

        $priceTypeTransfer = $this->createPriceTypeTransfer($priceProductEntity);
        $moneyValueTransfer = $this->createMoneyValueTransfer($priceProductStoreEntity, $priceProductStoreEntityData);
        $priceProductDimensionTransfer = $this->createPriceProductDimensionTransfer($priceProductStoreEntityData);

        return $this->mapPriceProductTransfer(
            $priceProductTransfer,
            $priceProductEntity,
            $priceTypeTransfer,
            $moneyValueTransfer,
            $priceProductDimensionTransfer,
            $priceProductStoreEntityData,
        );
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore[] $priceProductStoreEntities
     * @param array<string> $allowedProductSkus
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function mapPriceProductStoreEntitiesToPriceProductTransfers(ObjectCollection $priceProductStoreEntities, array $allowedProductSkus): array
    {
        $priceProductTransfers = [];

        foreach ($priceProductStoreEntities as $priceProductStoreEntity) {
            $priceProductTransfer = $this->mapPriceProductStoreEntityToPriceProductTransfer($priceProductStoreEntity, new PriceProductTransfer());

            if (!$this->hasSeveralConcretesInSameAbstract($priceProductStoreEntity)) {
                $priceProductTransfers[] = $priceProductTransfer;

                continue;
            }

            $priceProductTransfers = $this->duplicatePriceProductTransferPerProductEntity(
                $priceProductTransfers,
                $priceProductStoreEntity,
                $priceProductTransfer,
                $allowedProductSkus,
            );
        }

        return $priceProductTransfers;
    }

    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     *
     * @return bool
     */
    protected function hasSeveralConcretesInSameAbstract(SpyPriceProductStore $priceProductStoreEntity): bool
    {
        if (!$priceProductStoreEntity->getPriceProduct()->getSpyProductAbstract()) {
            return false;
        }

        /** @var \Orm\Zed\Product\Persistence\SpyProductAbstract $abstractProductEntity */
        $abstractProductEntity = $priceProductStoreEntity->getPriceProduct()
            ->getSpyProductAbstract();

        return $abstractProductEntity
            ->getSpyProducts()
            ->count() !== 1;
    }

    /**
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param array<string> $allowedProductSkus
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function duplicatePriceProductTransferPerProductEntity(
        array $priceProductTransfers,
        SpyPriceProductStore $priceProductStoreEntity,
        PriceProductTransfer $priceProductTransfer,
        array $allowedProductSkus
    ): array {
        /** @var \Orm\Zed\Product\Persistence\SpyProductAbstract $abstractProductEntity */
        $abstractProductEntity = $priceProductStoreEntity->getPriceProduct()
            ->getSpyProductAbstract();
        /** @var \Orm\Zed\Product\Persistence\SpyProduct[] $concreateProductEntities */
        $concreateProductEntities = $abstractProductEntity->getSpyProducts();

        foreach ($concreateProductEntities as $concreateProductEntity) {
            // Added due to propel entity cache system
            if (!in_array($concreateProductEntity->getSku(), $allowedProductSkus)) {
                continue;
            }

            $priceProductTransfers[] = (new PriceProductTransfer())
                ->fromArray($priceProductTransfer->toArray())
                ->setSkuProduct($concreateProductEntity->getSku());
        }

        return $priceProductTransfers;
    }

    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProduct $priceProductEntity
     *
     * @return \Generated\Shared\Transfer\PriceTypeTransfer
     */
    protected function createPriceTypeTransfer(SpyPriceProduct $priceProductEntity): PriceTypeTransfer
    {
        return (new PriceTypeTransfer())
            ->setIdPriceType($priceProductEntity->getPriceType()->getIdPriceType())
            ->setName($priceProductEntity->getPriceType()->getName())
            ->setPriceModeConfiguration($priceProductEntity->getPriceType()->getPriceModeConfiguration());
    }

    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     *
     * @return \Generated\Shared\Transfer\CurrencyTransfer
     */
    protected function createCurrencyTransfer(SpyPriceProductStore $priceProductStoreEntity): CurrencyTransfer
    {
        return (new CurrencyTransfer())
            ->fromArray($priceProductStoreEntity->getCurrency()->toArray(), true);
    }

    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     * @param array $priceProductStoreEntityData
     *
     * @return \Generated\Shared\Transfer\MoneyValueTransfer
     */
    protected function createMoneyValueTransfer(
        SpyPriceProductStore $priceProductStoreEntity,
        array $priceProductStoreEntityData
    ): MoneyValueTransfer {
        $currencyTransfer = $this->createCurrencyTransfer($priceProductStoreEntity);

        return (new MoneyValueTransfer())
            ->fromArray($priceProductStoreEntityData, true)
            ->setIdEntity($priceProductStoreEntity->getIdPriceProductStore())
            ->setNetAmount($priceProductStoreEntity->getNetPrice())
            ->setGrossAmount($priceProductStoreEntity->getGrossPrice())
            ->setCurrency($currencyTransfer);
    }

    /**
     * @param array $priceProductStoreEntityData
     *
     * @return \Generated\Shared\Transfer\PriceProductDimensionTransfer
     */
    protected function createPriceProductDimensionTransfer(array $priceProductStoreEntityData): PriceProductDimensionTransfer
    {
        return (new PriceProductDimensionTransfer())
            ->fromArray($priceProductStoreEntityData, true);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProduct $priceProductEntity
     * @param \Generated\Shared\Transfer\PriceTypeTransfer $priceTypeTransfer
     * @param \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer
     * @param \Generated\Shared\Transfer\PriceProductDimensionTransfer $priceProductDimensionTransfer
     * @param array $priceProductStoreEntityData
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function mapPriceProductTransfer(
        PriceProductTransfer $priceProductTransfer,
        SpyPriceProduct $priceProductEntity,
        PriceTypeTransfer $priceTypeTransfer,
        MoneyValueTransfer $moneyValueTransfer,
        PriceProductDimensionTransfer $priceProductDimensionTransfer,
        array $priceProductStoreEntityData
    ): PriceProductTransfer {
        /** @var \Orm\Zed\Product\Persistence\SpyProduct $productEntity */
        $productEntity = $priceProductEntity->getProduct();
        $sku = $priceProductEntity->getProduct() ? $productEntity->getSku() : $priceProductStoreEntityData['product_sku'];

        return $priceProductTransfer
            ->fromArray($priceProductStoreEntityData, true)
            ->setSkuProduct($sku)
            ->setIdProduct($priceProductEntity->getFkProduct())
            ->setIdProductAbstract($priceProductEntity->getFkProductAbstract())
            ->setPriceType($priceTypeTransfer)
            ->setPriceTypeName($priceTypeTransfer->getName())
            ->setMoneyValue($moneyValueTransfer)
            ->setPriceDimension($priceProductDimensionTransfer)
            ->setIsMergeable(true)
            ->setFkPriceType($priceTypeTransfer->getIdPriceType());
    }
}
