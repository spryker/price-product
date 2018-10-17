<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriter;

use Generated\Shared\Transfer\PriceProductTransfer;

interface PriceProductStoreWriterPluginExecutorInterface
{
    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    public function runPriceDimensionSaverPlugins(PriceProductTransfer $priceProductTransfer): PriceProductTransfer;

    /**
     * @param int $idPriceProductStore
     *
     * @return void
     */
    public function runPriceProductStorePreDeletePlugins(int $idPriceProductStore): void;
}
