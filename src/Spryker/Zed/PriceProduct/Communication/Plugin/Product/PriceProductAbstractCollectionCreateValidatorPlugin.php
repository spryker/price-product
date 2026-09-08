<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\PriceProduct\Communication\Plugin\Product;

use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductExtension\Dependency\Plugin\ProductAbstractCollectionCreateValidatorPluginInterface;

/**
 * @method \Spryker\Zed\PriceProduct\Business\PriceProductBusinessFactory getBusinessFactory()
 */
class PriceProductAbstractCollectionCreateValidatorPlugin extends AbstractPlugin implements ProductAbstractCollectionCreateValidatorPluginInterface
{
    /**
     * {@inheritDoc}
     * - Validates the store, currency and price type referenced by each price of `ProductAbstractTransfer.prices`.
     * - Adds an error per price referencing an unknown store, currency or price type, identifying the price by its index.
     * - Returns the response unchanged when no abstract product carries prices.
     *
     * @api
     */
    public function validate(
        ProductAbstractCollectionRequestTransfer $productAbstractCollectionRequestTransfer,
        ProductAbstractCollectionResponseTransfer $productAbstractCollectionResponseTransfer
    ): ProductAbstractCollectionResponseTransfer {
        return $this->getBusinessFactory()
            ->createProductPriceValidator()
            ->validateProductAbstractCollection($productAbstractCollectionRequestTransfer, $productAbstractCollectionResponseTransfer);
    }
}
