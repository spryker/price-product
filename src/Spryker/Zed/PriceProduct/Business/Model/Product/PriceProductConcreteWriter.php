<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Model\Product;

use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Spryker\Zed\PriceProduct\Business\Model\PriceType\PriceProductTypeReaderInterface;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToEventInterface;
use Spryker\Zed\PriceProduct\Dependency\PriceProductEvents;
use Spryker\Zed\PriceProduct\Persistence\PriceProductEntityManagerInterface;
use Spryker\Zed\PriceProduct\Persistence\PriceProductQueryContainerInterface;
use Spryker\Zed\PriceProduct\PriceProductConfig;
use Spryker\Zed\PropelOrm\Business\Transaction\DatabaseTransactionHandlerTrait;

class PriceProductConcreteWriter extends BaseProductPriceWriter implements PriceProductConcreteWriterInterface
{
    use DatabaseTransactionHandlerTrait;

    /**
     * @var \Spryker\Zed\PriceProduct\Business\Model\PriceType\PriceProductTypeReaderInterface
     */
    protected $priceTypeReader;

    /**
     * @var \Spryker\Zed\PriceProduct\Persistence\PriceProductQueryContainerInterface
     */
    protected $priceProductQueryContainer;

    /**
     * @var \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductDefaultWriterInterface
     */
    protected $priceProductDefaultWriter;

    /**
     * @var array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceDimensionConcreteSaverPluginInterface>
     */
    protected $priceDimensionConcreteSaverPlugins;

    /**
     * @var \Spryker\Zed\PriceProduct\Persistence\PriceProductEntityManagerInterface
     */
    protected $priceProductEntityManager;

    /**
     * @var \Spryker\Zed\PriceProduct\PriceProductConfig
     */
    protected $config;

    /**
     * @var \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriterInterface
     */
    protected $priceProductStoreWriter;

    /**
     * @var \Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToEventInterface
     */
    protected $eventFacade;

    /**
     * @param \Spryker\Zed\PriceProduct\Business\Model\PriceType\PriceProductTypeReaderInterface $priceTypeReader
     * @param \Spryker\Zed\PriceProduct\Persistence\PriceProductQueryContainerInterface $priceProductQueryContainer
     * @param \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductDefaultWriterInterface $priceProductDefaultWriter
     * @param array<\Spryker\Zed\PriceProductExtension\Dependency\Plugin\PriceDimensionConcreteSaverPluginInterface> $priceDimensionConcreteSaverPlugins
     * @param \Spryker\Zed\PriceProduct\Persistence\PriceProductEntityManagerInterface $priceProductEntityManager
     * @param \Spryker\Zed\PriceProduct\PriceProductConfig $config
     * @param \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriterInterface $priceProductStoreWriter
     * @param \Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToEventInterface $eventFacade
     */
    public function __construct(
        PriceProductTypeReaderInterface $priceTypeReader,
        PriceProductQueryContainerInterface $priceProductQueryContainer,
        PriceProductDefaultWriterInterface $priceProductDefaultWriter,
        array $priceDimensionConcreteSaverPlugins,
        PriceProductEntityManagerInterface $priceProductEntityManager,
        PriceProductConfig $config,
        PriceProductStoreWriterInterface $priceProductStoreWriter,
        PriceProductToEventInterface $eventFacade
    ) {
        $this->priceTypeReader = $priceTypeReader;
        $this->priceProductQueryContainer = $priceProductQueryContainer;
        $this->priceProductDefaultWriter = $priceProductDefaultWriter;
        $this->priceDimensionConcreteSaverPlugins = $priceDimensionConcreteSaverPlugins;
        $this->priceProductEntityManager = $priceProductEntityManager;
        $this->config = $config;
        $this->priceProductStoreWriter = $priceProductStoreWriter;
        $this->eventFacade = $eventFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function persistProductConcretePriceCollection(
        ProductConcreteTransfer $productConcreteTransfer
    ): ProductConcreteTransfer {
        return $this->handleDatabaseTransaction(function () use ($productConcreteTransfer) {
            return $this->executePersistProductConcretePriceCollectionTransaction($productConcreteTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected function executePersistProductConcretePriceCollectionTransaction(
        ProductConcreteTransfer $productConcreteTransfer
    ): ProductConcreteTransfer {
        foreach ($productConcreteTransfer->getPrices() as $priceProductTransfer) {
            /** @var \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer */
            $moneyValueTransfer = $priceProductTransfer->requireMoneyValue()->getMoneyValue();
            if ($this->isEmptyMoneyValue($moneyValueTransfer)) {
                continue;
            }

            $this->executePersistProductConcretePrice($productConcreteTransfer, $priceProductTransfer);
        }

        $this->triggerProductUpdateEvent($productConcreteTransfer->getIdProductConcreteOrFail());

        return $productConcreteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function executePersistProductConcretePrice(
        ProductConcreteTransfer $productConcreteTransfer,
        PriceProductTransfer $priceProductTransfer
    ): PriceProductTransfer {
        /** @var int $idProductConcrete */
        $idProductConcrete = $productConcreteTransfer
            ->requireIdProductConcrete()
            ->getIdProductConcrete();

        if (!$priceProductTransfer->getPriceDimension()) {
            $priceProductTransfer->setPriceDimension(
                (new PriceProductDimensionTransfer())
                    ->setType($this->config->getPriceDimensionDefault()),
            );
        }

        $this->persistProductConcretePriceEntity($priceProductTransfer, $idProductConcrete);

        $priceProductTransfer->setIdProduct($idProductConcrete);
        $priceProductTransfer->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract());
        $priceProductTransfer = $this->priceProductStoreWriter->persistPriceProductStore($priceProductTransfer);

        return $priceProductTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function executePriceDimensionConcreteSaverPlugins(
        PriceProductTransfer $priceProductTransfer
    ): PriceProductTransfer {
        /** @var \Generated\Shared\Transfer\PriceProductDimensionTransfer $priceDimensionTransfer */
        $priceDimensionTransfer = $priceProductTransfer->requirePriceDimension()->getPriceDimension();
        /** @var string $priceDimensionType */
        $priceDimensionType = $priceDimensionTransfer->getType();

        if ($priceDimensionType === $this->config->getPriceDimensionDefault()) {
            $priceProductDefaultEntityTransfer = $this->priceProductDefaultWriter->persistPriceProductDefault($priceProductTransfer);

            /** @var int $idPriceProductDefault */
            $idPriceProductDefault = $priceProductDefaultEntityTransfer->getIdPriceProductDefault();
            $priceDimensionTransfer->setIdPriceProductDefault(
                $idPriceProductDefault,
            );

            return $priceProductTransfer->setPriceDimension($priceDimensionTransfer);
        }

        foreach ($this->priceDimensionConcreteSaverPlugins as $priceDimensionConcreteSaverPlugin) {
            if ($priceDimensionConcreteSaverPlugin->getDimensionName() !== $priceDimensionType) {
                continue;
            }

            return $priceDimensionConcreteSaverPlugin->savePrice($priceProductTransfer);
        }

        return $priceProductTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param int $idProductConcrete
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function persistProductConcretePriceEntity(
        PriceProductTransfer $priceProductTransfer,
        int $idProductConcrete
    ): PriceProductTransfer {
        /** @var \Generated\Shared\Transfer\PriceTypeTransfer $priceTypeTransfer */
        $priceTypeTransfer = $priceProductTransfer->requirePriceType()->getPriceType();
        /** @var string $priceTypeName */
        $priceTypeName = $priceTypeTransfer->requireName()->getName();
        $priceTypeEntity = $this->priceTypeReader->getPriceTypeByName($priceTypeName);

        $priceProductEntity = $this->priceProductQueryContainer
            ->queryPriceProductForConcreteProductBy($idProductConcrete, $priceTypeEntity->getIdPriceType())
            ->findOneOrCreate();

        $priceProductEntity
            ->setFkProduct($idProductConcrete)
            ->save();

        $priceProductTransfer->setIdPriceProduct($priceProductEntity->getIdPriceProduct());

        return $priceProductTransfer;
    }

    /**
     * @param int $idProductConcrete
     *
     * @return void
     */
    protected function triggerProductUpdateEvent(int $idProductConcrete): void
    {
        $productUpdatedEvent = (new EventEntityTransfer())->setId($idProductConcrete);

        $this->eventFacade->trigger(PriceProductEvents::PRODUCT_CONCRETE_UPDATE, $productUpdatedEvent);
    }
}
