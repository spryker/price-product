<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Model\Product;

use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\PriceTypeTransfer;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProduct;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore;
use Spryker\Zed\PriceProduct\Business\Model\PriceType\ProductPriceTypeMapperInterface;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToCurrencyFacadeInterface;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToPriceFacadeInterface;
use Spryker\Zed\PriceProduct\PriceProductConfig;

class PriceProductMapper implements PriceProductMapperInterface
{
    /**
     * @var string
     */
    protected static $netPriceModeIdentifier;

    /**
     * @var string
     */
    protected static $grossPriceModeIdentifier;

    /**
     * @var \Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToCurrencyFacadeInterface
     */
    protected $currencyFacade;

    /**
     * @var \Spryker\Zed\PriceProduct\Business\Model\PriceType\ProductPriceTypeMapperInterface
     */
    protected $priceProductTypeMapper;

    /**
     * @var \Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToPriceFacadeInterface
     */
    protected $priceFacade;

    /**
     * @var \Spryker\Zed\PriceProduct\PriceProductConfig
     */
    protected $config;

    public function __construct(
        PriceProductToCurrencyFacadeInterface $currencyFacade,
        ProductPriceTypeMapperInterface $priceProductTypeMapper,
        PriceProductToPriceFacadeInterface $priceFacade,
        PriceProductConfig $config
    ) {
        $this->currencyFacade = $currencyFacade;
        $this->priceProductTypeMapper = $priceProductTypeMapper;
        $this->priceFacade = $priceFacade;
        $this->config = $config;
    }

    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProduct $priceProductEntity
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    public function mapProductPriceTransfer(
        SpyPriceProductStore $priceProductStoreEntity,
        SpyPriceProduct $priceProductEntity
    ) {
        $moneyValueTransfer = $this->mapMoneyValueTransfer($priceProductStoreEntity);
        $priceTypeTransfer = $this->priceProductTypeMapper->mapFromEntity($priceProductEntity->getPriceType());

        return (new PriceProductTransfer())
            ->fromArray($priceProductEntity->toArray(), true)
            ->setIdProduct($priceProductEntity->getFkProduct())
            ->setIdProductAbstract($priceProductEntity->getFkProductAbstract())
            ->setPriceTypeName($priceTypeTransfer->getName())
            ->setPriceType($priceTypeTransfer)
            ->setMoneyValue($moneyValueTransfer);
    }

    /**
     * @return string
     */
    public function getGrossPriceModeIdentifier()
    {
        if (!static::$grossPriceModeIdentifier) {
            static::$grossPriceModeIdentifier = $this->priceFacade->getGrossPriceModeIdentifier();
        }

        return static::$grossPriceModeIdentifier;
    }

    /**
     * @return string
     */
    public function getNetPriceModeIdentifier()
    {
        if (!static::$netPriceModeIdentifier) {
            static::$netPriceModeIdentifier = $this->priceFacade->getNetPriceModeIdentifier();
        }

        return static::$netPriceModeIdentifier;
    }

    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     *
     * @return \Generated\Shared\Transfer\MoneyValueTransfer
     */
    protected function mapMoneyValueTransfer(SpyPriceProductStore $priceProductStoreEntity)
    {
        $currencyTransfer = $this->currencyFacade
            ->getByIdCurrency($priceProductStoreEntity->getFkCurrency());

        return (new MoneyValueTransfer())
            ->fromArray($priceProductStoreEntity->toArray(), true)
            ->setIdEntity($priceProductStoreEntity->getPrimaryKey())
            ->setNetAmount($priceProductStoreEntity->getNetPrice())
            ->setGrossAmount($priceProductStoreEntity->getGrossPrice())
            ->setCurrency($currencyTransfer);
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore> $priceProductStoreEntities
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function mapPriceProductStoreEntitiesToPriceProductTransfers(
        $priceProductStoreEntities
    ): array {
        $productPriceCollection = [];
        foreach ($priceProductStoreEntities as $priceProductStoreEntity) {
            $productPriceCollection[] = $this->mapPriceProductStoreEntityToTransfer(
                $priceProductStoreEntity,
            );
        }

        return $productPriceCollection;
    }

    protected function mapPriceProductStoreEntityToTransfer(
        SpyPriceProductStore $priceProductStoreEntity
    ): PriceProductTransfer {
        $priceProductEntity = $priceProductStoreEntity->getPriceProduct();

        $priceTypeTransfer = (new PriceTypeTransfer())
            ->setIdPriceType($priceProductEntity->getPriceType()->getIdPriceType())
            ->setName($priceProductEntity->getPriceType()->getName())
            ->setPriceModeConfiguration($priceProductEntity->getPriceType()->getPriceModeConfiguration());

        $moneyValueTransfer = $this->mapMoneyValueTransfer($priceProductStoreEntity);

        $priceProductDimensionTransfer = $this->getPriceProductDimensionTransfer(
            $priceProductStoreEntity,
        );

        return (new PriceProductTransfer())
            ->fromArray($priceProductEntity->toArray(), true)
            ->setIdProduct($priceProductEntity->getFkProduct())
            ->setIdProductAbstract($priceProductEntity->getFkProductAbstract())
            ->setPriceType($priceTypeTransfer)
            ->setPriceTypeName($priceTypeTransfer->getName())
            ->setMoneyValue($moneyValueTransfer)
            ->setPriceDimension($priceProductDimensionTransfer)
            ->setIsMergeable(true)
            ->setSkuProduct($this->findProductSku($priceProductEntity));
    }

    protected function getPriceProductDimensionTransfer(
        SpyPriceProductStore $priceProductStoreEntity
    ): PriceProductDimensionTransfer {
        $priceProductDimensionTransfer = (new PriceProductDimensionTransfer())
            ->fromArray(
                $priceProductStoreEntity->getVirtualColumns(),
                true,
            );

        return $priceProductDimensionTransfer;
    }

    protected function findProductSku(SpyPriceProduct $priceProductEntity): ?string
    {
        $productEntity = $priceProductEntity->getProduct();
        if ($productEntity === null) {
            return null;
        }

        return $productEntity->getSku();
    }
}
