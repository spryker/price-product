<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\PriceProduct;

use Spryker\Service\Kernel\AbstractService;

/**
 * @method \Spryker\Service\PriceProduct\PriceProductServiceFactory getFactory()
 */
class PriceProductService extends AbstractService implements PriceProductServiceInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PriceProductTransfer[] $priceProductTransfers
     * @param \Generated\Shared\Transfer\PriceProductFilterTransfer $priceProductFilterTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer|null
     */
    public function resolveProductPriceByPriceProductFilter(array $priceProductTransfers, PriceProductFilterTransfer $priceProductFilterTransfer): ?PriceProductTransfer
    {
        return $this->getFactory()
            ->createPriceProductMatcher()
            ->matchPriceByFilter($priceProductTransfers, $priceProductFilterTransfer);
    }
}
