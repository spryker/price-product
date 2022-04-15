<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Persistence;

use Generated\Shared\Transfer\PriceProductCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\SpyPriceProductDefaultEntityTransfer;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductDefault;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\PriceProduct\Persistence\PriceProductPersistenceFactory getFactory()
 */
class PriceProductEntityManager extends AbstractEntityManager implements PriceProductEntityManagerInterface
{
    /**
     * @deprecated Use {@link \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriter::deleteOrphanPriceProductStoreEntities()} instead.
     *
     * @return void
     */
    public function deleteOrphanPriceProductStoreEntities(): void
    {
        $priceProductStoreQuery = $this->getFactory()
            ->createPriceProductStoreQuery();

        $this->getFactory()
            ->createPriceProductDimensionQueryExpander()
            ->expandPriceProductStoreQueryWithPriceDimensionForDelete(
                $priceProductStoreQuery,
                new PriceProductCriteriaTransfer(),
            );

        if (!$priceProductStoreQuery->getAsColumns()) {
            return;
        }

        $priceProductStoreQuery->find()->delete();
    }

    /**
     * @param int $idPriceProductStore
     *
     * @return void
     */
    public function deletePriceProductStore(int $idPriceProductStore): void
    {
        $priceProductStoreEntity = $this->getFactory()
            ->createPriceProductStoreQuery()
            ->filterByIdPriceProductStore($idPriceProductStore)
            ->findOne();

        if (!$priceProductStoreEntity) {
            return;
        }

        $priceProductStoreEntity->delete();
    }

    /**
     * @param \Generated\Shared\Transfer\SpyPriceProductDefaultEntityTransfer $spyPriceProductDefaultEntityTransfer
     *
     * @return \Generated\Shared\Transfer\SpyPriceProductDefaultEntityTransfer
     */
    public function savePriceProductDefaultEntity(
        SpyPriceProductDefaultEntityTransfer $spyPriceProductDefaultEntityTransfer
    ): SpyPriceProductDefaultEntityTransfer {
        $priceProductMapper = $this->getFactory()->createPriceProductMapper();
        $priceProductDefaultEntity = $priceProductMapper->mapPriceProductDefaultTransferToPriceProductEntity(
            $spyPriceProductDefaultEntityTransfer,
            new SpyPriceProductDefault(),
        );
        $priceProductDefaultEntity->save();

        return $priceProductMapper->mapPriceProductDefaultEntityToPriceProductDefaultTransfer(
            $priceProductDefaultEntity,
            $spyPriceProductDefaultEntityTransfer,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return void
     */
    public function deletePriceProductStoreByPriceProductTransfer(PriceProductTransfer $priceProductTransfer): void
    {
        /** @var \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer */
        $moneyValueTransfer = $priceProductTransfer->requireMoneyValue()->getMoneyValue();
        /** @var \Generated\Shared\Transfer\CurrencyTransfer $currencyTransfer */
        $currencyTransfer = $moneyValueTransfer->requireCurrency()->getCurrency();

        $moneyValueTransfer
            ->requireCurrency();

        $this->getFactory()
            ->createPriceProductStoreQuery()
            ->filterByFkCurrency($currencyTransfer->getIdCurrency())
            ->filterByFkPriceProduct($priceProductTransfer->getIdPriceProduct())
            ->filterByFkStore($moneyValueTransfer->getFkStore())
            ->find()
            ->delete();
    }

    /**
     * @param int $idPriceProduct
     *
     * @return void
     */
    public function deletePriceProductById(int $idPriceProduct): void
    {
        $this->getFactory()
            ->createPriceProductQuery()
            ->filterByIdPriceProduct($idPriceProduct)
            ->find()
            ->delete();
    }

    /**
     * @param int $idPriceProductStore
     *
     * @return void
     */
    public function deletePriceProductDefaultsByPriceProductStoreId(int $idPriceProductStore): void
    {
        $this->getFactory()
            ->createPriceProductDefaultQuery()
            ->filterByFkPriceProductStore($idPriceProductStore)
            ->find()
            ->delete();
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductCollectionDeleteCriteriaTransfer $priceProductCollectionDeleteCriteriaTransfer
     *
     * @return void
     */
    public function deletePriceProductDefaults(
        PriceProductCollectionDeleteCriteriaTransfer $priceProductCollectionDeleteCriteriaTransfer
    ): void {
        if (!$priceProductCollectionDeleteCriteriaTransfer->getPriceProductDefaultIds()) {
            return;
        }
        $priceProductDefaultCollection = $this->getFactory()
            ->createPriceProductDefaultQuery()
            ->filterByIdPriceProductDefault_In(
                $priceProductCollectionDeleteCriteriaTransfer->getPriceProductDefaultIds(),
            )
            ->filterByFkPriceProductStore_In(
                $priceProductCollectionDeleteCriteriaTransfer->getPriceProductStoreIds(),
            )
            ->find();

        $priceProductDefaultCollection->delete();
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return int
     */
    public function savePriceProductForProductConcrete(PriceProductTransfer $priceProductTransfer): int
    {
        $priceProductTransfer
            ->requireFkPriceType()
            ->requireIdProduct();

        /** @var \Orm\Zed\PriceProduct\Persistence\SpyPriceProduct $priceProductEntity */
        $priceProductEntity = $this->getFactory()
            ->createPriceProductQuery()
            ->filterByFKProduct($priceProductTransfer->getIdProduct())
            ->filterByFkPriceType($priceProductTransfer->getFkPriceType())
            ->findOneOrCreate();

        $priceProductEntity->save();

        return $priceProductEntity->getIdPriceProduct();
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return int
     */
    public function savePriceProductForProductAbstract(PriceProductTransfer $priceProductTransfer): int
    {
        $priceProductTransfer
            ->requireFkPriceType()
            ->requireIdProductAbstract();

        /** @var \Orm\Zed\PriceProduct\Persistence\SpyPriceProduct $priceProductEntity */
        $priceProductEntity = $this->getFactory()
            ->createPriceProductQuery()
            ->filterByFkProductAbstract($priceProductTransfer->getIdProductAbstract())
            ->filterByFkPriceType($priceProductTransfer->getFkPriceType())
            ->findOneOrCreate();

        $priceProductEntity->save();

        return $priceProductEntity->getIdPriceProduct();
    }
}
