<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\PriceProduct\Merger;

interface PriceProductMergerInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $abstractPriceProductTransfers
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $concretePriceProductTransfers
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function mergeConcreteAndAbstractPrices(array $abstractPriceProductTransfers, array $concretePriceProductTransfers): array;
}
