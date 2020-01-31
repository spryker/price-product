<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\PriceProduct;

use Spryker\Service\Kernel\AbstractServiceFactory;
use Spryker\Service\PriceProduct\PriceProductMatcher\PriceProductMatcher;
use Spryker\Service\PriceProduct\PriceProductMatcher\PriceProductMatcherInterface;

class PriceProductServiceFactory extends AbstractServiceFactory
{
    /**
     * @return \Spryker\Service\PriceProduct\PriceProductMatcher\PriceProductMatcherInterface
     */
    public function createPriceProductMatcher(): PriceProductMatcherInterface
    {
        return new PriceProductMatcher();
    }
}
