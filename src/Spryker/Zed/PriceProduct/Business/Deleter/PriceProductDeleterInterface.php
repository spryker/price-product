<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Deleter;

use Generated\Shared\Transfer\PriceProductCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductCollectionResponseTransfer;

interface PriceProductDeleterInterface
{
    public function deletePriceProductCollection(
        PriceProductCollectionDeleteCriteriaTransfer $priceProductCollectionDeleteCriteriaTransfer
    ): PriceProductCollectionResponseTransfer;
}
