<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProduct\Business\Facade;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\PriceTypeTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use SprykerTest\Zed\PriceProduct\PriceProductBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProduct
 * @group Business
 * @group Facade
 * @group PersistProductConcretePriceCollectionWithReferencesTest
 * Add your own group annotations below this line
 */
class PersistProductConcretePriceCollectionWithReferencesTest extends Unit
{
    protected PriceProductBusinessTester $tester;

    public function testPersistProductConcretePriceCollectionResolvesFromCurrencyCodeAndStoreName(): void
    {
        // Arrange
        $priceProductFacade = $this->tester->getFacade();
        $productConcreteTransfer = $this->tester->haveProduct();

        $currencyTransfer = $this->tester->getCurrencyFacade()->fromIsoCode(PriceProductBusinessTester::EUR_ISO_CODE);
        $storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => 'DE']);

        $priceProductTransfer = $this->createPriceProductWithCodeAndName(
            $productConcreteTransfer->getIdProductConcrete(),
            $productConcreteTransfer->getFkProductAbstract(),
            $productConcreteTransfer->getAbstractSku(),
            $productConcreteTransfer->getSku(),
            $priceProductFacade->getDefaultPriceTypeName(),
            PriceProductBusinessTester::EUR_ISO_CODE,
            $storeTransfer->getName(),
            10,
            9,
        );

        $productConcreteTransfer->setPrices(new ArrayObject([$priceProductTransfer]));

        // Act
        $productConcreteTransfer = $priceProductFacade->persistProductConcretePriceCollection($productConcreteTransfer);

        // Assert
        foreach ($productConcreteTransfer->getPrices() as $persistedPriceProductTransfer) {
            $this->assertNotEmpty($persistedPriceProductTransfer->getIdPriceProduct());
            $this->assertNotEmpty($persistedPriceProductTransfer->getMoneyValue()->getIdEntity());
            $this->assertSame($currencyTransfer->getIdCurrency(), $persistedPriceProductTransfer->getMoneyValue()->getFkCurrency());
            $this->assertSame($storeTransfer->getIdStore(), $persistedPriceProductTransfer->getMoneyValue()->getFkStore());
        }
    }

    public function testPersistProductConcretePriceCollectionWithPresetForeignKeysPersistsCorrectly(): void
    {
        // Arrange
        $priceProductFacade = $this->tester->getFacade();
        $productConcreteTransfer = $this->tester->haveProduct();

        $priceTypeTransfer = (new PriceTypeTransfer())
            ->setName($priceProductFacade->getDefaultPriceTypeName());

        $priceProductTransfer = $this->tester->createPriceProductTransfer(
            $productConcreteTransfer,
            $priceTypeTransfer,
            10,
            9,
            PriceProductBusinessTester::EUR_ISO_CODE,
        );

        $productConcreteTransfer->setPrices(new ArrayObject([$priceProductTransfer]));

        // Act
        $productConcreteTransfer = $priceProductFacade->persistProductConcretePriceCollection($productConcreteTransfer);

        // Assert
        foreach ($productConcreteTransfer->getPrices() as $persistedPriceProductTransfer) {
            $this->assertNotEmpty($persistedPriceProductTransfer->getIdPriceProduct());
            $this->assertNotEmpty($persistedPriceProductTransfer->getMoneyValue()->getIdEntity());
        }
    }

    public function testPersistProductConcretePriceCollectionResolvesOnlyUnresolvedEntriesInMixedCollection(): void
    {
        // Arrange
        $priceProductFacade = $this->tester->getFacade();
        $productConcreteTransfer = $this->tester->haveProduct();

        $priceTypeTransfer = (new PriceTypeTransfer())
            ->setName($priceProductFacade->getDefaultPriceTypeName());

        $resolvedPriceProductTransfer = $this->tester->createPriceProductTransfer(
            $productConcreteTransfer,
            $priceTypeTransfer,
            10,
            9,
            PriceProductBusinessTester::EUR_ISO_CODE,
        );
        $originalFkCurrency = $resolvedPriceProductTransfer->getMoneyValue()->getFkCurrency();
        $originalFkStore = $resolvedPriceProductTransfer->getMoneyValue()->getFkStore();

        $storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => 'DE']);
        $unresolvedPriceProductTransfer = $this->createPriceProductWithCodeAndName(
            $productConcreteTransfer->getIdProductConcrete(),
            $productConcreteTransfer->getFkProductAbstract(),
            $productConcreteTransfer->getAbstractSku(),
            $productConcreteTransfer->getSku(),
            $priceProductFacade->getDefaultPriceTypeName(),
            PriceProductBusinessTester::USD_ISO_CODE,
            $storeTransfer->getName(),
            20,
            18,
        );

        $productConcreteTransfer->setPrices(new ArrayObject([
            $resolvedPriceProductTransfer,
            $unresolvedPriceProductTransfer,
        ]));

        // Act
        $productConcreteTransfer = $priceProductFacade->persistProductConcretePriceCollection($productConcreteTransfer);

        // Assert
        $prices = $productConcreteTransfer->getPrices();
        $this->assertCount(2, $prices);

        $this->assertSame($originalFkCurrency, $prices[0]->getMoneyValue()->getFkCurrency());
        $this->assertSame($originalFkStore, $prices[0]->getMoneyValue()->getFkStore());
        $this->assertNotEmpty($prices[0]->getIdPriceProduct());

        $this->assertNotNull($prices[1]->getMoneyValue()->getFkCurrency());
        $this->assertNotNull($prices[1]->getMoneyValue()->getFkStore());
        $this->assertNotEmpty($prices[1]->getIdPriceProduct());
    }

    public function testPersistProductConcretePriceCollectionSetsFullTransferObjectsOnResolvedEntries(): void
    {
        // Arrange
        $priceProductFacade = $this->tester->getFacade();
        $productConcreteTransfer = $this->tester->haveProduct();

        $storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => 'DE']);

        $priceProductTransfer = $this->createPriceProductWithCodeAndName(
            $productConcreteTransfer->getIdProductConcrete(),
            $productConcreteTransfer->getFkProductAbstract(),
            $productConcreteTransfer->getAbstractSku(),
            $productConcreteTransfer->getSku(),
            $priceProductFacade->getDefaultPriceTypeName(),
            PriceProductBusinessTester::EUR_ISO_CODE,
            $storeTransfer->getName(),
            10,
            9,
        );

        $productConcreteTransfer->setPrices(new ArrayObject([$priceProductTransfer]));

        // Act
        $productConcreteTransfer = $priceProductFacade->persistProductConcretePriceCollection($productConcreteTransfer);

        // Assert
        $moneyValueTransfer = $productConcreteTransfer->getPrices()[0]->getMoneyValue();

        $this->assertNotNull($moneyValueTransfer->getFkCurrency());
        $this->assertNotNull($moneyValueTransfer->getCurrency());
        $this->assertSame(PriceProductBusinessTester::EUR_ISO_CODE, $moneyValueTransfer->getCurrency()->getCode());
        $this->assertSame($moneyValueTransfer->getFkCurrency(), $moneyValueTransfer->getCurrency()->getIdCurrency());

        $this->assertNotNull($moneyValueTransfer->getFkStore());
        $this->assertNotNull($moneyValueTransfer->getStore());
        $this->assertSame('DE', $moneyValueTransfer->getStore()->getName());
        $this->assertSame($moneyValueTransfer->getFkStore(), $moneyValueTransfer->getStore()->getIdStore());
    }

    protected function createPriceProductWithCodeAndName(
        int $idProductConcrete,
        int $idProductAbstract,
        string $abstractSku,
        string $concreteSku,
        string $priceTypeName,
        string $currencyCode,
        string $storeName,
        int $grossPrice,
        int $netPrice,
    ): PriceProductTransfer {
        $config = $this->tester->createSharedPriceProductConfig();

        $moneyValueTransfer = (new MoneyValueTransfer())
            ->setGrossAmount($grossPrice)
            ->setNetAmount($netPrice)
            ->setCurrency((new CurrencyTransfer())->setCode($currencyCode))
            ->setStore((new StoreTransfer())->setName($storeName));

        return (new PriceProductTransfer())
            ->setIdProduct($idProductConcrete)
            ->setIdProductAbstract($idProductAbstract)
            ->setSkuProductAbstract($abstractSku)
            ->setSkuProduct($concreteSku)
            ->setPriceTypeName($priceTypeName)
            ->setPriceType((new PriceTypeTransfer())->setName($priceTypeName))
            ->setPriceDimension(
                (new PriceProductDimensionTransfer())->setType($config->getPriceDimensionDefault()),
            )
            ->setMoneyValue($moneyValueTransfer);
    }
}
