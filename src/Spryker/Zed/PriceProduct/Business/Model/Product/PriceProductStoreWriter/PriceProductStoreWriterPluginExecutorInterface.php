<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriter;

use Generated\Shared\Transfer\PriceProductTransfer;

interface PriceProductStoreWriterPluginExecutorInterface
{
    public function executePriceDimensionAbstractSaverPlugins(PriceProductTransfer $priceProductTransfer): PriceProductTransfer;

    public function executePriceDimensionConcreteSaverPlugins(PriceProductTransfer $priceProductTransfer): PriceProductTransfer;

    public function executePriceProductStorePreDeletePlugins(int $idPriceProductStore): void;

    public function executeOrphanPriceProductStoreRemovalVoterPlugins(): ?bool;
}
