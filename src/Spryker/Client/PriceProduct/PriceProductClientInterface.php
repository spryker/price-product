<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProduct;

use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\PriceProductFilterTransfer;

/**
 * @method \Spryker\Client\PriceProduct\PriceProductFactory getFactory()
 * @method \Spryker\Client\PriceProduct\PriceProductConfig getConfig()
 */
interface PriceProductClientInterface
{
    /**
     * Specification:
     * - Returns default price type as configured for current environment
     *
     * @api
     *
     * @return string
     */
    public function getPriceTypeDefaultName();

    /**
     * Specification:
     *  - Resolves current product price as per current customer state, it will try to resolve price based on customer selected currency and price mode.
     *  - Executes stack of {@link \Spryker\Client\PriceProductExtension\Dependency\Plugin\PriceProductPostResolvePluginInterface} plugins.
     *  - Defaults to price mode defined in environment configuration if customer not yet selected.
     *  - Price map structure:
     *  [
     *       "EUR" => [
     *           "GROSS_MODE" => [
     *               "DEFAULT" => 9999,
     *               "ORIGINAL" => 12564,
     *           ],
     *           "NET_MODE" => [
     *               "DEFAULT" => 8999,
     *               "ORIGINAL" => 11308,
     *           ],
     *       ],
     *       "CHF" => [
     *           "GROSS_MODE" => [
     *               "DEFAULT" => 11499,
     *               "ORIGINAL" => 14449,
     *           ],
     *           "NET_MODE" => [
     *               "DEFAULT" => 10349,
     *               "ORIGINAL" => 13004,
     *           ],
     *       ],
     *   ],
     *
     * @api
     *
     * @deprecated Use resolveProductPriceTransfer() instead.
     *
     * @param array $priceMap
     *
     * @return \Generated\Shared\Transfer\CurrentProductPriceTransfer
     */
    public function resolveProductPrice(array $priceMap);

    /**
     * Specification:
     *  - Resolves current product price as per current customer state, it will try to resolve price based on customer selected currency and price mode.
     *  - Executes stack of {@link \Spryker\Client\PriceProductExtension\Dependency\Plugin\PriceProductPostResolvePluginInterface} plugins.
     *  - Defaults to price mode defined in environment configuration if customer not yet selected.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     *
     * @return \Generated\Shared\Transfer\CurrentProductPriceTransfer
     */
    public function resolveProductPriceTransfer(array $priceProductTransfers): CurrentProductPriceTransfer;

    /**
     * Specification:
     * - Resolves current product price as per current customer state, it will try to resolve price based on customer selected currency and price mode.
     * - Executes stack of {@link \Spryker\Client\PriceProductExtension\Dependency\Plugin\PriceProductPostResolvePluginInterface} plugins.
     * - Uses price product filter to resolve product price.
     * - Defaults to price mode defined in environment configuration if customer not yet selected.
     * - Considers quantity when provided for sum price calculation.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     * @param \Generated\Shared\Transfer\PriceProductFilterTransfer $priceProductFilterTransfer
     *
     * @return \Generated\Shared\Transfer\CurrentProductPriceTransfer
     */
    public function resolveProductPriceTransferByPriceProductFilter(
        array $priceProductTransfers,
        PriceProductFilterTransfer $priceProductFilterTransfer
    ): CurrentProductPriceTransfer;
}
