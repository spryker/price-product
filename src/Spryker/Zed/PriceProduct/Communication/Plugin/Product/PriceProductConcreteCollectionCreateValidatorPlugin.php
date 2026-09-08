<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\PriceProduct\Communication\Plugin\Product;

use Generated\Shared\Transfer\ProductConcreteCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductConcreteCollectionResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductExtension\Dependency\Plugin\ProductConcreteCollectionCreateValidatorPluginInterface;

/**
 * @method \Spryker\Zed\PriceProduct\Business\PriceProductBusinessFactory getBusinessFactory()
 */
class PriceProductConcreteCollectionCreateValidatorPlugin extends AbstractPlugin implements ProductConcreteCollectionCreateValidatorPluginInterface
{
    /**
     * {@inheritDoc}
     * - Validates the store, currency and price type referenced by each price of `ProductConcreteTransfer.prices`.
     * - Adds an error per price referencing an unknown store, currency or price type, identifying the price by its index.
     * - Returns the response unchanged when no concrete product carries prices.
     *
     * @api
     */
    public function validate(
        ProductConcreteCollectionRequestTransfer $productConcreteCollectionRequestTransfer,
        ProductConcreteCollectionResponseTransfer $productConcreteCollectionResponseTransfer
    ): ProductConcreteCollectionResponseTransfer {
        return $this->getBusinessFactory()
            ->createProductPriceValidator()
            ->validateProductConcreteCollection($productConcreteCollectionRequestTransfer, $productConcreteCollectionResponseTransfer);
    }
}
