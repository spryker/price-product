<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Persistence;

use ArrayObject;
use Generated\Shared\Transfer\PriceProductCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\QueryCriteriaTransfer;
use Generated\Shared\Transfer\SpyPriceProductDefaultEntityTransfer;

interface PriceProductRepositoryInterface
{
    /**
     * @param string $concreteSku
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findProductConcretePricesBySkuAndCriteria(
        string $concreteSku,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;

    /**
     * @param string $abstractSku
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findProductAbstractPricesBySkuAndCriteria(
        string $abstractSku,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;

    /**
     * @param int $idProductConcrete
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findProductConcretePricesByIdAndCriteria(
        int $idProductConcrete,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;

    /**
     * @param array<int> $productConcreteIds
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function getProductConcretePricesByIdsAndCriteria(
        array $productConcreteIds,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;

    /**
     * @param int $idProductAbstract
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findProductAbstractPricesByIdAndCriteria(
        int $idProductAbstract,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;

    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findProductAbstractPricesByIdIn(array $productAbstractIds): array;

    public function buildDefaultPriceDimensionQueryCriteria(
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): ?QueryCriteriaTransfer;

    public function buildUnconditionalDefaultPriceDimensionQueryCriteria(): QueryCriteriaTransfer;

    /**
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findPriceProductTransfersWithOrphanPriceProductStore(PriceProductCriteriaTransfer $priceProductCriteriaTransfer): array;

    public function findPriceProductDefaultByIdPriceProductStore(int $idPriceProductStore): ?SpyPriceProductDefaultEntityTransfer;

    public function findIdPriceProductForProductConcrete(PriceProductTransfer $priceProductTransfer): ?int;

    public function findIdPriceProductForProductAbstract(PriceProductTransfer $priceProductTransfer): ?int;

    /**
     * @param array<int> $productAbstractIds
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer|null $priceProductCriteriaTransfer
     *
     * @return array<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findProductAbstractPricesByIdInAndCriteria(
        array $productAbstractIds,
        ?PriceProductCriteriaTransfer $priceProductCriteriaTransfer = null
    ): array;

    public function findIdPriceProductStoreByPriceProduct(PriceProductTransfer $priceProductTransfer): ?int;

    public function isPriceProductUsedForOtherCurrencyAndStore(PriceProductTransfer $priceProductTransfer): bool;

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return array<\Generated\Shared\Transfer\SpyPriceProductStoreEntityTransfer>
     */
    public function findPriceProductStoresByPriceProduct(PriceProductTransfer $priceProductTransfer): array;

    /**
     * @param array<string> $concreteSkus
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function getProductAbstractPricesByConcreteSkusAndCriteria(
        array $concreteSkus,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;

    /**
     * @param array<string> $concreteSkus
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function getProductConcretePricesByConcreteSkusAndCriteria(
        array $concreteSkus,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;

    public function isPriceProductByProductIdentifierAndPriceTypeExists(
        PriceProductTransfer $priceProductTransfer
    ): bool;

    /**
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function getProductPricesByCriteria(PriceProductCriteriaTransfer $priceProductCriteriaTransfer): ArrayObject;

    /**
     * @param array<int> $productConcreteIds
     * @param array<int> $productAbstractIds
     * @param \Generated\Shared\Transfer\PriceProductCriteriaTransfer $priceProductCriteriaTransfer
     *
     * @return array
     */
    public function findProductPricesByConcreteIdsOrAbstractIds(
        array $productConcreteIds,
        array $productAbstractIds,
        PriceProductCriteriaTransfer $priceProductCriteriaTransfer
    ): array;
}
