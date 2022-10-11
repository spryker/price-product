<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\PriceProduct\Communication\Plugin\DefaultPriceQueryCriteriaPlugin;
use Spryker\Zed\PriceProduct\Dependency\External\PriceProductToValidationAdapter;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToCurrencyFacadeBridge;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToEventBridge;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToPriceFacadeBridge;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToProductFacadeBridge;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToStoreFacadeBridge;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToTouchFacadeBridge;
use Spryker\Zed\PriceProduct\Dependency\Service\PriceProductToUtilEncodingServiceBridge;

/**
 * @method \Spryker\Zed\PriceProduct\PriceProductConfig getConfig()
 */
class PriceProductDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_EVENT = 'FACADE_EVENT';

    /**
     * @var string
     */
    public const FACADE_TOUCH = 'FACADE_TOUCH';

    /**
     * @var string
     */
    public const FACADE_PRODUCT = 'FACADE_PRODUCT';

    /**
     * @var string
     */
    public const FACADE_CURRENCY = 'FACADE_CURRENCY';

    /**
     * @var string
     */
    public const FACADE_PRICE = 'FACADE_PRICE';

    /**
     * @var string
     */
    public const FACADE_STORE = 'FACADE_STORE';

    /**
     * @var string
     */
    public const SERVICE_PRICE_PRODUCT = 'SERVICE_PRICE_PRODUCT';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_DIMENSION_QUERY_CRITERIA = 'PLUGIN_PRICE_DIMENSION_QUERY_CRITERIA';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_DIMENSION_ABSTRACT_SAVER = 'PLUGIN_PRICE_DIMENSION_ABSTRACT_SAVER';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_DIMENSION_CONCRETE_SAVER = 'PLUGIN_PRICE_DIMENSION_CONCRETE_SAVER';

    /**
     * @var string
     */
    public const PLUGINS_ORPHAN_PRICE_PRODUCT_STORE_REMOVAL_VOTER = 'PLUGINS_ORPHAN_PRICE_PRODUCT_STORE_REMOVAL_VOTER';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_PRODUCT_DIMENSION_TRANSFER_EXPANDER = 'PLUGIN_PRICE_PRODUCT_DIMENSION_TRANSFER_EXPANDER';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_PRODUCT_PRICES_EXTRACTOR = 'PLUGIN_PRICE_PRODUCT_PRICES_EXTRACTOR';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_PRODUCT_STORE_PRE_DELETE = 'PLUGIN_PRICE_PRODUCT_STORE_PRE_DELETE';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_PRODUCT_EXTERNAL_PROVIDER = 'PLUGIN_PRICE_PRODUCT_PROVIDER';

    /**
     * @var string
     */
    public const PLUGIN_PRICE_PRODUCT_VALIDATOR = 'PLUGIN_PRICE_PRODUCT_VALIDATOR';

    /**
     * @var string
     */
    public const PLUGINS_PRICE_PRODUCT_COLLECTION_DELETE = 'PLUGINS_PRICE_PRODUCT_COLLECTION_DELETE';

    /**
     * @var string
     */
    public const EXTERNAL_ADAPTER_VALIDATION = 'EXTERNAL_ADAPTER_VALIDATION';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = $this->addEventFacade($container);
        $container = $this->addTouchFacade($container);
        $container = $this->addProductFacade($container);
        $container = $this->addCurrencyFacade($container);
        $container = $this->addPriceFacade($container);
        $container = $this->addStoreFacade($container);
        $container = $this->addPriceProductService($container);
        $container = $this->addPriceDimensionAbstractSaverPlugins($container);
        $container = $this->addPriceDimensionConcreteSaverPlugins($container);
        $container = $this->addPriceProductExternalProviderPlugins($container);
        $container = $this->addPriceProductDimensionExpanderStrategyPlugins($container);
        $container = $this->addPriceProductPricesExtractorPlugins($container);
        $container = $this->addPriceProductStorePreDeletePlugins($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addValidationAdapter($container);
        $container = $this->addPriceProductValidatorPlugins($container);
        $container = $this->addPriceProductCollectionDeletePlugins($container);
        $container = $this->addOrphanPriceProductStoreRemovalVoterPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function providePersistenceLayerDependencies(Container $container)
    {
        $container = $this->addPriceDimensionQueryCriteriaPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addEventFacade(Container $container): Container
    {
        $container->set(static::FACADE_EVENT, function (Container $container) {
            return new PriceProductToEventBridge($container->getLocator()->event()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addTouchFacade(Container $container)
    {
        $container->set(static::FACADE_TOUCH, function (Container $container) {
            return new PriceProductToTouchFacadeBridge($container->getLocator()->touch()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductFacade(Container $container)
    {
        $container->set(static::FACADE_PRODUCT, function (Container $container) {
            return new PriceProductToProductFacadeBridge($container->getLocator()->product()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCurrencyFacade(Container $container)
    {
        $container->set(static::FACADE_CURRENCY, function (Container $container) {
            return new PriceProductToCurrencyFacadeBridge($container->getLocator()->currency()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceFacade(Container $container)
    {
        $container->set(static::FACADE_PRICE, function (Container $container) {
            return new PriceProductToPriceFacadeBridge($container->getLocator()->price()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStoreFacade(Container $container)
    {
        $container->set(static::FACADE_STORE, function (Container $container) {
            return new PriceProductToStoreFacadeBridge($container->getLocator()->store()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceDimensionQueryCriteriaPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_DIMENSION_QUERY_CRITERIA, function (Container $container) {
            return $this->getPriceDimensionQueryCriteriaPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceDimensionAbstractSaverPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_DIMENSION_ABSTRACT_SAVER, function (Container $container) {
            return $this->getPriceDimensionAbstractSaverPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceDimensionConcreteSaverPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_DIMENSION_CONCRETE_SAVER, function (Container $container) {
            return $this->getPriceDimensionConcreteSaverPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceProductDimensionExpanderStrategyPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_PRODUCT_DIMENSION_TRANSFER_EXPANDER, function (Container $container) {
            return $this->getPriceProductDimensionExpanderStrategyPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceProductPricesExtractorPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_PRODUCT_PRICES_EXTRACTOR, function (Container $container) {
            return $this->getPriceProductPricesExtractorPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceProductStorePreDeletePlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_PRODUCT_STORE_PRE_DELETE, function () {
            return $this->getPriceProductStorePreDeletePlugins();
        });

        return $container;
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceProductExternalProviderPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_PRODUCT_EXTERNAL_PROVIDER, function () {
            return $this->getPriceProductExternalProviderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addOrphanPriceProductStoreRemovalVoterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_ORPHAN_PRICE_PRODUCT_STORE_REMOVAL_VOTER, function () {
            return $this->getOrphanPriceProductStoreRemovalVoterPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new PriceProductToUtilEncodingServiceBridge($container->getLocator()->utilEncoding()->service());
        });

        return $container;
    }

    /**
     * The plugins in this stack will provide additional criteria to main price product query.
     *
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceDimensionQueryCriteriaPluginInterface>
     */
    protected function getPriceDimensionQueryCriteriaPlugins(): array
    {
        return [
            new DefaultPriceQueryCriteriaPlugin(),
        ];
    }

    /**
     * The plugins are executed when saving abstract product price
     *
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceDimensionAbstractSaverPluginInterface>
     */
    protected function getPriceDimensionAbstractSaverPlugins(): array
    {
        return [];
    }

    /**
     * The plugins are executed before deleting price product store entity
     *
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceProductStorePreDeletePluginInterface>
     */
    protected function getPriceProductStorePreDeletePlugins(): array
    {
        return [];
    }

    /**
     * The plugins are executed when saving concrete product price
     *
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceDimensionConcreteSaverPluginInterface>
     */
    protected function getPriceDimensionConcreteSaverPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Service\PriceProductExtension\Dependency\Plugin\PriceProductDimensionExpanderStrategyPluginInterface>
     */
    protected function getPriceProductDimensionExpanderStrategyPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceProductReaderPricesExtractorPluginInterface>
     */
    protected function getPriceProductPricesExtractorPlugins(): array
    {
        return [];
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceProductExternalProviderPluginInterface>
     */
    protected function getPriceProductExternalProviderPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceProductService(Container $container): Container
    {
        $container->set(static::SERVICE_PRICE_PRODUCT, function (Container $container) {
            return $container->getLocator()->priceProduct()->service();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addValidationAdapter(Container $container): Container
    {
        $container->set(static::EXTERNAL_ADAPTER_VALIDATION, function () {
            return new PriceProductToValidationAdapter();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceProductValidatorPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_PRICE_PRODUCT_VALIDATOR, function () {
            return $this->getPriceProductValidatorPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPriceProductCollectionDeletePlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRICE_PRODUCT_COLLECTION_DELETE, function () {
            return $this->getPriceProductCollectionDeletePlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceProductValidatorPluginInterface>
     */
    protected function getPriceProductValidatorPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceProductCollectionDeletePluginInterface>
     */
    protected function getPriceProductCollectionDeletePlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\OrphanPriceProductStoreRemovalVoterPluginInterface>
     */
    protected function getOrphanPriceProductStoreRemovalVoterPlugins(): array
    {
        return [];
    }
}
