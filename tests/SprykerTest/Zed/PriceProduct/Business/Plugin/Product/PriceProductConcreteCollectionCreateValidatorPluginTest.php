<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProduct\Business\Plugin\Product;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductConcreteCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductConcreteCollectionResponseTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\PriceProduct\Communication\Plugin\Product\PriceProductConcreteCollectionCreateValidatorPlugin;
use SprykerTest\Zed\PriceProduct\PriceProductBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProduct
 * @group Business
 * @group Plugin
 * @group Product
 * @group PriceProductConcreteCollectionCreateValidatorPluginTest
 *
 * Add your own group annotations below this line
 */
class PriceProductConcreteCollectionCreateValidatorPluginTest extends Unit
{
    protected const string UNKNOWN_STORE_NAME = 'ZZ-non-existent-store';

    protected const int NET_AMOUNT = 1000;

    protected const string PRODUCT_SKU = 'concrete-sku';

    protected PriceProductBusinessTester $tester;

    public function testValidateReturnsErrorWhenPriceHasNeitherNetNorGrossAmount(): void
    {
        // Arrange
        $productConcreteCollectionRequestTransfer = $this->createRequestWithPrice(new PriceProductTransfer());

        // Act
        $productConcreteCollectionResponseTransfer = (new PriceProductConcreteCollectionCreateValidatorPlugin())->validate(
            $productConcreteCollectionRequestTransfer,
            new ProductConcreteCollectionResponseTransfer(),
        );

        // Assert
        $this->assertCount(1, $productConcreteCollectionResponseTransfer->getErrors());
        $this->assertSame(static::PRODUCT_SKU, $productConcreteCollectionResponseTransfer->getErrors()->offsetGet(0)->getEntityIdentifier());
    }

    public function testValidateReturnsErrorWhenPriceReferencesUnknownStore(): void
    {
        // Arrange
        $productConcreteCollectionRequestTransfer = $this->createRequestWithPrice(
            (new PriceProductTransfer())->setMoneyValue(
                (new MoneyValueTransfer())
                    ->setNetAmount(static::NET_AMOUNT)
                    ->setStore((new StoreTransfer())->setName(static::UNKNOWN_STORE_NAME)),
            ),
        );

        // Act
        $productConcreteCollectionResponseTransfer = (new PriceProductConcreteCollectionCreateValidatorPlugin())->validate(
            $productConcreteCollectionRequestTransfer,
            new ProductConcreteCollectionResponseTransfer(),
        );

        // Assert
        $this->assertCount(1, $productConcreteCollectionResponseTransfer->getErrors());
    }

    public function testValidatePassesWhenPriceReferencesKnownStoreWithNetAmount(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore();
        $productConcreteCollectionRequestTransfer = $this->createRequestWithPrice(
            (new PriceProductTransfer())->setMoneyValue(
                (new MoneyValueTransfer())
                    ->setNetAmount(static::NET_AMOUNT)
                    ->setStore((new StoreTransfer())->setName($storeTransfer->getName())),
            ),
        );

        // Act
        $productConcreteCollectionResponseTransfer = (new PriceProductConcreteCollectionCreateValidatorPlugin())->validate(
            $productConcreteCollectionRequestTransfer,
            new ProductConcreteCollectionResponseTransfer(),
        );

        // Assert
        $this->assertCount(0, $productConcreteCollectionResponseTransfer->getErrors());
    }

    public function testValidatePassesWhenNoPricesProvided(): void
    {
        // Arrange
        $productConcreteCollectionRequestTransfer = (new ProductConcreteCollectionRequestTransfer())
            ->addProduct((new ProductConcreteTransfer())->setSku(static::PRODUCT_SKU));

        // Act
        $productConcreteCollectionResponseTransfer = (new PriceProductConcreteCollectionCreateValidatorPlugin())->validate(
            $productConcreteCollectionRequestTransfer,
            new ProductConcreteCollectionResponseTransfer(),
        );

        // Assert
        $this->assertCount(0, $productConcreteCollectionResponseTransfer->getErrors());
    }

    protected function createRequestWithPrice(PriceProductTransfer $priceProductTransfer): ProductConcreteCollectionRequestTransfer
    {
        return (new ProductConcreteCollectionRequestTransfer())
            ->addProduct(
                (new ProductConcreteTransfer())
                    ->setSku(static::PRODUCT_SKU)
                    ->addPrice($priceProductTransfer),
            );
    }
}
