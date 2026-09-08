<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\PriceProduct\Business\Product\Validator;

use ArrayObject;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Generated\Shared\Transfer\ProductConcreteCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductConcreteCollectionResponseTransfer;
use Spryker\Zed\PriceProduct\Business\Model\PriceType\PriceProductTypeReaderInterface;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToCurrencyFacadeInterface;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToStoreFacadeInterface;

class ProductPriceValidator implements ProductPriceValidatorInterface
{
    public function __construct(
        protected readonly PriceProductToStoreFacadeInterface $storeFacade,
        protected readonly PriceProductToCurrencyFacadeInterface $currencyFacade,
        protected readonly PriceProductTypeReaderInterface $priceTypeReader,
    ) {
    }

    public function validateProductConcreteCollection(
        ProductConcreteCollectionRequestTransfer $productConcreteCollectionRequestTransfer,
        ProductConcreteCollectionResponseTransfer $productConcreteCollectionResponseTransfer
    ): ProductConcreteCollectionResponseTransfer {
        $priceProductTransfersBySku = [];

        foreach ($productConcreteCollectionRequestTransfer->getProducts() as $productConcreteTransfer) {
            if ($productConcreteTransfer->getPrices()->count() !== 0) {
                $priceProductTransfersBySku[(string)$productConcreteTransfer->getSku()] = $productConcreteTransfer->getPrices();
            }
        }

        foreach ($this->collectErrors($priceProductTransfersBySku) as $errorTransfer) {
            $productConcreteCollectionResponseTransfer->addError($errorTransfer);
        }

        return $productConcreteCollectionResponseTransfer;
    }

    public function validateProductAbstractCollection(
        ProductAbstractCollectionRequestTransfer $productAbstractCollectionRequestTransfer,
        ProductAbstractCollectionResponseTransfer $productAbstractCollectionResponseTransfer
    ): ProductAbstractCollectionResponseTransfer {
        $priceProductTransfersBySku = [];

        foreach ($productAbstractCollectionRequestTransfer->getProductAbstracts() as $productAbstractTransfer) {
            if ($productAbstractTransfer->getPrices()->count() !== 0) {
                $priceProductTransfersBySku[(string)$productAbstractTransfer->getSku()] = $productAbstractTransfer->getPrices();
            }
        }

        foreach ($this->collectErrors($priceProductTransfersBySku) as $errorTransfer) {
            $productAbstractCollectionResponseTransfer->addError($errorTransfer);
        }

        return $productAbstractCollectionResponseTransfer;
    }

    /**
     * @param array<string, \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>> $priceProductTransfersBySku
     *
     * @return list<\Generated\Shared\Transfer\ErrorTransfer>
     */
    protected function collectErrors(array $priceProductTransfersBySku): array
    {
        if ($priceProductTransfersBySku === []) {
            return [];
        }

        $knownStoreNames = $this->getKnownStoreNames($priceProductTransfersBySku);
        $knownCurrencyCodes = $this->getKnownCurrencyCodes($priceProductTransfersBySku);

        $errorTransfers = [];

        foreach ($priceProductTransfersBySku as $sku => $priceProductTransfers) {
            foreach ($priceProductTransfers as $index => $priceProductTransfer) {
                foreach ($this->validateSinglePrice($priceProductTransfer, (int)$index, $knownStoreNames, $knownCurrencyCodes) as $message) {
                    $errorTransfers[] = (new ErrorTransfer())
                        ->setEntityIdentifier((string)$sku)
                        ->setMessage($message);
                }
            }
        }

        return $errorTransfers;
    }

    /**
     * @param list<string> $knownStoreNames
     * @param list<string> $knownCurrencyCodes
     *
     * @return list<string>
     */
    protected function validateSinglePrice(
        PriceProductTransfer $priceProductTransfer,
        int $index,
        array $knownStoreNames,
        array $knownCurrencyCodes
    ): array {
        $messages = [];
        $moneyValueTransfer = $priceProductTransfer->getMoneyValue();

        $storeName = $moneyValueTransfer?->getStore()?->getName();

        if ($storeName !== null && !in_array($storeName, $knownStoreNames, true)) {
            $messages[] = sprintf('Price #%d references unknown store "%s".', $index, $storeName);
        }

        $currencyCode = $moneyValueTransfer?->getCurrency()?->getCode();

        if ($currencyCode !== null && !in_array($currencyCode, $knownCurrencyCodes, true)) {
            $messages[] = sprintf('Price #%d references unknown currency "%s".', $index, $currencyCode);
        }

        $priceTypeName = $priceProductTransfer->getPriceType()?->getName()
            ?? $priceProductTransfer->getPriceTypeName();

        if ($priceTypeName !== null && !$this->priceTypeReader->hasPriceType($priceTypeName)) {
            $messages[] = sprintf('Price #%d references unknown price type "%s".', $index, $priceTypeName);
        }

        if ($moneyValueTransfer?->getNetAmount() === null && $moneyValueTransfer?->getGrossAmount() === null) {
            $messages[] = sprintf('Price #%d must define at least one of "netAmount" or "grossAmount".', $index);
        }

        return $messages;
    }

    /**
     * @param array<string, \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>> $priceProductTransfersBySku
     *
     * @return list<string>
     */
    protected function getKnownStoreNames(array $priceProductTransfersBySku): array
    {
        $requestedStoreNames = [];

        foreach ($priceProductTransfersBySku as $priceProductTransfers) {
            $requestedStoreNames = array_merge($requestedStoreNames, $this->extractStoreNames($priceProductTransfers));
        }

        $requestedStoreNames = array_values(array_unique($requestedStoreNames));

        if ($requestedStoreNames === []) {
            return [];
        }

        $knownStoreNames = [];

        foreach ($this->storeFacade->getStoreTransfersByStoreNames($requestedStoreNames) as $storeTransfer) {
            $storeName = $storeTransfer->getName();

            if ($storeName !== null) {
                $knownStoreNames[] = $storeName;
            }
        }

        return $knownStoreNames;
    }

    /**
     * @param array<string, \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>> $priceProductTransfersBySku
     *
     * @return list<string>
     */
    protected function getKnownCurrencyCodes(array $priceProductTransfersBySku): array
    {
        $requestedCurrencyCodes = [];

        foreach ($priceProductTransfersBySku as $priceProductTransfers) {
            $requestedCurrencyCodes = array_merge($requestedCurrencyCodes, $this->extractCurrencyCodes($priceProductTransfers));
        }

        $requestedCurrencyCodes = array_values(array_unique($requestedCurrencyCodes));

        if ($requestedCurrencyCodes === []) {
            return [];
        }

        $knownCurrencyCodes = [];

        foreach ($this->currencyFacade->getCurrencyTransfersByIsoCodes($requestedCurrencyCodes) as $currencyTransfer) {
            $currencyCode = $currencyTransfer->getCode();

            if ($currencyCode !== null) {
                $knownCurrencyCodes[] = $currencyCode;
            }
        }

        return $knownCurrencyCodes;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     *
     * @return list<string>
     */
    protected function extractStoreNames(ArrayObject $priceProductTransfers): array
    {
        $storeNames = [];

        foreach ($priceProductTransfers as $priceProductTransfer) {
            $storeName = $priceProductTransfer->getMoneyValue()?->getStore()?->getName();

            if ($storeName !== null && $storeName !== '') {
                $storeNames[] = $storeName;
            }
        }

        return array_values(array_unique($storeNames));
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     *
     * @return list<string>
     */
    protected function extractCurrencyCodes(ArrayObject $priceProductTransfers): array
    {
        $currencyCodes = [];

        foreach ($priceProductTransfers as $priceProductTransfer) {
            $currencyCode = $priceProductTransfer->getMoneyValue()?->getCurrency()?->getCode();

            if ($currencyCode !== null && $currencyCode !== '') {
                $currencyCodes[] = $currencyCode;
            }
        }

        return array_values(array_unique($currencyCodes));
    }
}
