<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProduct;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToCurrencyClientInterface;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToPriceClientInterface;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToQuoteClientInterface;
use Spryker\Client\PriceProduct\ProductPriceResolver\ProductPriceResolver;
use Spryker\Service\PriceProduct\PriceProductServiceInterface;

/**
 * @method \Spryker\Client\PriceProduct\PriceProductConfig getConfig()
 */
class PriceProductFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Client\PriceProduct\ProductPriceResolver\ProductPriceResolverInterface
     */
    public function createProductPriceResolver()
    {
        return new ProductPriceResolver(
            $this->getPriceClient(),
            $this->getCurrencyClient(),
            $this->getConfig(),
            $this->getQuoteClient(),
            $this->getPriceProductService(),
            $this->getPriceProductPostResolvePlugins(),
        );
    }

    public function getPriceProductService(): PriceProductServiceInterface
    {
        return $this->getProvidedDependency(PriceProductDependencyProvider::SERVICE_PRICE_PRODUCT);
    }

    public function getPriceClient(): PriceProductToPriceClientInterface
    {
        return $this->getProvidedDependency(PriceProductDependencyProvider::CLIENT_PRICE);
    }

    public function getCurrencyClient(): PriceProductToCurrencyClientInterface
    {
        return $this->getProvidedDependency(PriceProductDependencyProvider::CLIENT_CURRENCY);
    }

    public function getQuoteClient(): PriceProductToQuoteClientInterface
    {
        return $this->getProvidedDependency(PriceProductDependencyProvider::CLIENT_QUOTE);
    }

    /**
     * @return \Spryker\Client\PriceProduct\PriceProductConfig
     */
    public function getModuleConfig()
    {
        /** @var \Spryker\Client\PriceProduct\PriceProductConfig $config */
        $config = parent::getConfig();

        return $config;
    }

    /**
     * @return list<\Spryker\Client\PriceProductExtension\Dependency\Plugin\PriceProductPostResolvePluginInterface>
     */
    public function getPriceProductPostResolvePlugins(): array
    {
        return $this->getProvidedDependency(PriceProductDependencyProvider::PLUGINS_PRICE_PRODUCT_POST_RESOLVE);
    }
}
