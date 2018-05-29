<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProduct;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToCurrencyClientBridge;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToPriceClientBridge;

class PriceProductDependencyProvider extends AbstractDependencyProvider
{
    public const CLIENT_PRICE = 'PRICE_CLIENT';
    public const CLIENT_CURRENCY = 'CURRENCY_CLIENT';
    public const PLUGIN_PRICE_DIMENSION = 'PLUGIN_PRICE_DIMENSION';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container)
    {
        $container = $this->addPriceProductClient($container);
        $container = $this->addCurrencyClient($container);
        $container = $this->addPriceDimensionPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addPriceProductClient(Container $container)
    {
        $container[static::CLIENT_PRICE] = function (Container $container) {
            return new PriceProductToPriceClientBridge($container->getLocator()->price()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addCurrencyClient(Container $container)
    {
        $container[static::CLIENT_CURRENCY] = function (Container $container) {
            return new PriceProductToCurrencyClientBridge($container->getLocator()->currency()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addPriceDimensionPlugins(Container $container)
    {
        $container[static::PLUGIN_PRICE_DIMENSION] = function (Container $container) {
            return $this->getPriceDimensionPlugins();
        };

        return $container;
    }

    /**
     * @return \Spryker\Client\PriceProduct\Dependency\Plugin\PriceDimensionPluginInterface[]
     */
    protected function getPriceDimensionPlugins()
    {
        return [];
    }
}
