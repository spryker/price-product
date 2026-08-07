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
use Generated\Shared\Transfer\SpyPriceProductDefaultEntityTransfer;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProduct;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductDefault;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore;
use Orm\Zed\PriceProduct\Persistence\SpyPriceType;

class PriceProductMapper
{
    /**
     * @var array<int, \Orm\Zed\PriceProduct\Persistence\SpyPriceType>
     */
    protected static array $priceTypeCache = [];

    /**
     * @var array<int, \Generated\Shared\Transfer\CurrencyTransfer>
     */
    protected static array $currencyCache = [];

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
     * @param array<\Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore> $priceProductStoreEntities
     * @param array<string>|null $allowedProductSkus
     * @param array<int, array<string>> $productSkusGroupedByIdProductAbstract
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function mapPriceProductStoreEntitiesToPriceProductTransfers(
        array $priceProductStoreEntities,
        ?array $allowedProductSkus = null,
        array $productSkusGroupedByIdProductAbstract = []
    ): array {
        $priceProductTransfers = [];

        foreach ($priceProductStoreEntities as $priceProductStoreEntity) {
            $priceProductTransfer = $this->mapPriceProductStoreEntityToPriceProductTransfer($priceProductStoreEntity, new PriceProductTransfer());

            if ($allowedProductSkus === null || !$this->hasSeveralConcretesInSameAbstract($priceProductStoreEntity, $productSkusGroupedByIdProductAbstract)) {
                $priceProductTransfers[] = $priceProductTransfer;

                continue;
            }

            $priceProductTransfers = $this->duplicatePriceProductTransferPerProductEntity(
                $priceProductTransfers,
                $priceProductStoreEntity,
                $priceProductTransfer,
                $allowedProductSkus,
                $productSkusGroupedByIdProductAbstract,
            );
        }

        return $priceProductTransfers;
    }

    /**
     * @param array<int, array<string>> $productSkusGroupedByIdProductAbstract
     */
    protected function hasSeveralConcretesInSameAbstract(
        SpyPriceProductStore $priceProductStoreEntity,
        array $productSkusGroupedByIdProductAbstract
    ): bool {
        if ($priceProductStoreEntity->getPriceProduct()->getFkProductAbstract() === null) {
            return false;
        }

        return count($this->getConcreteProductSkusForAbstract($priceProductStoreEntity, $productSkusGroupedByIdProductAbstract)) !== 1;
    }

    /**
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param array<string> $allowedProductSkus
     * @param array<int, array<string>> $productSkusGroupedByIdProductAbstract
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function duplicatePriceProductTransferPerProductEntity(
        array $priceProductTransfers,
        SpyPriceProductStore $priceProductStoreEntity,
        PriceProductTransfer $priceProductTransfer,
        array $allowedProductSkus,
        array $productSkusGroupedByIdProductAbstract
    ): array {
        $productSkus = $this->getConcreteProductSkusForAbstract($priceProductStoreEntity, $productSkusGroupedByIdProductAbstract);

        foreach ($productSkus as $productSku) {
            if (!in_array($productSku, $allowedProductSkus)) {
                continue;
            }

            $priceProductTransfers[] = (new PriceProductTransfer())
                ->fromArray($priceProductTransfer->toArray())
                ->setSkuProduct($productSku);
        }

        return $priceProductTransfers;
    }

    /**
     * @param array<int, array<string>> $productSkusGroupedByIdProductAbstract
     *
     * @return array<string>
     */
    protected function getConcreteProductSkusForAbstract(
        SpyPriceProductStore $priceProductStoreEntity,
        array $productSkusGroupedByIdProductAbstract
    ): array {
        if ($productSkusGroupedByIdProductAbstract !== []) {
            $idProductAbstract = $priceProductStoreEntity->getPriceProduct()->getFkProductAbstract();

            return $productSkusGroupedByIdProductAbstract[$idProductAbstract] ?? [];
        }

        $abstractProductEntity = $priceProductStoreEntity->getPriceProduct()->getSpyProductAbstract();
        if ($abstractProductEntity === null) {
            return [];
        }

        $productSkus = [];
        foreach ($abstractProductEntity->getSpyProducts() as $spyProductEntity) {
            $productSkus[] = $spyProductEntity->getSku();
        }

        return $productSkus;
    }

    public function mapPriceProductDefaultTransferToPriceProductEntity(
        SpyPriceProductDefaultEntityTransfer $priceProductDefaultTransfer,
        SpyPriceProductDefault $priceProductDefaultEntity
    ): SpyPriceProductDefault {
        $priceProductDefaultEntity->fromArray($priceProductDefaultTransfer->toArray());
        if ($priceProductDefaultEntity->getPrimaryKey()) {
            $priceProductDefaultEntity->setNew(false);
        }

        return $priceProductDefaultEntity;
    }

    public function mapPriceProductDefaultEntityToPriceProductDefaultTransfer(
        SpyPriceProductDefault $priceProductDefaultEntity,
        SpyPriceProductDefaultEntityTransfer $priceProductDefaultTransfer
    ): SpyPriceProductDefaultEntityTransfer {
        return $priceProductDefaultTransfer->fromArray($priceProductDefaultEntity->toArray());
    }

    protected function createPriceTypeTransfer(SpyPriceProduct $priceProductEntity): PriceTypeTransfer
    {
        $priceType = $this->getPriceType($priceProductEntity);

        return (new PriceTypeTransfer())
            ->setIdPriceType($priceType->getIdPriceType())
            ->setName($priceType->getName())
            ->setPriceModeConfiguration($priceType->getPriceModeConfiguration());
    }

    protected function getPriceType(SpyPriceProduct $priceProductEntity): SpyPriceType
    {
        if (!isset(static::$priceTypeCache[$priceProductEntity->getFkPriceType()])) {
            static::$priceTypeCache[$priceProductEntity->getFkPriceType()] = $priceProductEntity->getPriceType();
        }

        return static::$priceTypeCache[$priceProductEntity->getFkPriceType()];
    }

    protected function createCurrencyTransfer(SpyPriceProductStore $priceProductStoreEntity): CurrencyTransfer
    {
        if (!isset(static::$currencyCache[$priceProductStoreEntity->getFkCurrency()])) {
            static::$currencyCache[$priceProductStoreEntity->getFkCurrency()] = (new CurrencyTransfer())
                ->fromArray($priceProductStoreEntity->getCurrency()->toArray(), true);
        }

        return static::$currencyCache[$priceProductStoreEntity->getFkCurrency()];
    }

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

    protected function createPriceProductDimensionTransfer(array $priceProductStoreEntityData): PriceProductDimensionTransfer
    {
        return (new PriceProductDimensionTransfer())
            ->fromArray($priceProductStoreEntityData, true);
    }

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
        $productSku = array_key_exists('product_sku', $priceProductStoreEntityData) ? $priceProductStoreEntityData['product_sku'] : null;

        $sku = $priceProductEntity->getProduct() ? $productEntity->getSku() : $productSku;

        return $priceProductTransfer
            ->fromArray($priceProductEntity->toArray(), true)
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
