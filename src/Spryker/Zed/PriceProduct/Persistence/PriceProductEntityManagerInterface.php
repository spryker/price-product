<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Persistence;

use Generated\Shared\Transfer\PriceProductCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\SpyPriceProductDefaultEntityTransfer;

/**
 * @method \Spryker\Zed\PriceProduct\Persistence\PriceProductPersistenceFactory getFactory()
 */
interface PriceProductEntityManagerInterface
{
    public function deleteOrphanPriceProductStoreEntities(): void;

    public function deletePriceProductStore(int $idPriceProductStore): void;

    public function savePriceProductDefaultEntity(
        SpyPriceProductDefaultEntityTransfer $spyPriceProductDefaultEntityTransfer
    ): SpyPriceProductDefaultEntityTransfer;

    public function deletePriceProductById(int $idPriceProduct): void;

    public function deletePriceProductStoreByPriceProductTransfer(PriceProductTransfer $priceProductTransfer): void;

    public function deletePriceProductDefaultsByPriceProductStoreId(int $idPriceProductStore): void;

    public function deletePriceProductDefaults(PriceProductCollectionDeleteCriteriaTransfer $priceProductCollectionDeleteCriteriaTransfer): void;

    public function savePriceProductForProductConcrete(PriceProductTransfer $priceProductTransfer): int;

    public function savePriceProductForProductAbstract(PriceProductTransfer $priceProductTransfer): int;
}
