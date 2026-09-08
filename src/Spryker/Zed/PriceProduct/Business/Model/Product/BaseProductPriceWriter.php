<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Model\Product;

use ArrayObject;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToCurrencyFacadeInterface;
use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToStoreFacadeInterface;

class BaseProductPriceWriter
{
    public function __construct(
        protected PriceProductToCurrencyFacadeInterface $currencyFacade,
        protected PriceProductToStoreFacadeInterface $storeFacade,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer
     *
     * @return bool
     */
    protected function isEmptyMoneyValue(MoneyValueTransfer $moneyValueTransfer)
    {
        return (!$moneyValueTransfer->getIdEntity() && $moneyValueTransfer->getNetAmount() === null && $moneyValueTransfer->getGrossAmount() === null);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     */
    protected function resolveMoneyValueIdentifiers(ArrayObject $priceProductTransfers): void
    {
        $currencyCodesToResolve = [];
        $storeNamesToResolve = [];

        foreach ($priceProductTransfers as $priceProductTransfer) {
            $moneyValueTransfer = $priceProductTransfer->getMoneyValue();

            if ($moneyValueTransfer === null) {
                continue;
            }

            if ($moneyValueTransfer->getFkCurrency() === null && $moneyValueTransfer->getCurrency() !== null) {
                $code = $moneyValueTransfer->getCurrency()->getCode();

                if ($code !== null) {
                    $currencyCodesToResolve[$code] = true;
                }
            }

            if ($moneyValueTransfer->getFkStore() === null && $moneyValueTransfer->getStore() !== null) {
                $name = $moneyValueTransfer->getStore()->getName();

                if ($name !== null) {
                    $storeNamesToResolve[$name] = true;
                }
            }
        }

        $currencyTransfersIndexedByCode = $this->resolveCurrencies(array_keys($currencyCodesToResolve));
        $storeTransfersIndexedByName = $this->resolveStores(array_keys($storeNamesToResolve));

        foreach ($priceProductTransfers as $priceProductTransfer) {
            $moneyValueTransfer = $priceProductTransfer->getMoneyValue();

            if ($moneyValueTransfer === null) {
                continue;
            }

            $this->applyResolvedCurrency($moneyValueTransfer, $currencyTransfersIndexedByCode);
            $this->applyResolvedStore($moneyValueTransfer, $storeTransfersIndexedByName);
        }
    }

    /**
     * @param array<string> $currencyCodes
     *
     * @return array<string, \Generated\Shared\Transfer\CurrencyTransfer>
     */
    protected function resolveCurrencies(array $currencyCodes): array
    {
        if ($currencyCodes === []) {
            return [];
        }

        $indexed = [];

        foreach ($this->currencyFacade->getCurrencyTransfersByIsoCodes($currencyCodes) as $currencyTransfer) {
            $indexed[$currencyTransfer->getCodeOrFail()] = $currencyTransfer;
        }

        return $indexed;
    }

    /**
     * @param array<string> $storeNames
     *
     * @return array<string, \Generated\Shared\Transfer\StoreTransfer>
     */
    protected function resolveStores(array $storeNames): array
    {
        if ($storeNames === []) {
            return [];
        }

        $indexed = [];

        foreach ($this->storeFacade->getStoreTransfersByStoreNames($storeNames) as $storeTransfer) {
            $indexed[$storeTransfer->getNameOrFail()] = $storeTransfer;
        }

        return $indexed;
    }

    /**
     * @param array<string, \Generated\Shared\Transfer\CurrencyTransfer> $currencyTransfersIndexedByCode
     */
    protected function applyResolvedCurrency(MoneyValueTransfer $moneyValueTransfer, array $currencyTransfersIndexedByCode): void
    {
        if ($moneyValueTransfer->getFkCurrency() !== null) {
            return;
        }

        $currency = $moneyValueTransfer->getCurrency();

        if ($currency === null || $currency->getCode() === null) {
            return;
        }

        $resolvedCurrency = $currencyTransfersIndexedByCode[$currency->getCode()] ?? null;

        if ($resolvedCurrency === null) {
            return;
        }

        $moneyValueTransfer->setFkCurrency($resolvedCurrency->getIdCurrencyOrFail());
        $moneyValueTransfer->setCurrency($resolvedCurrency);
    }

    /**
     * @param array<string, \Generated\Shared\Transfer\StoreTransfer> $storeTransfersIndexedByName
     */
    protected function applyResolvedStore(MoneyValueTransfer $moneyValueTransfer, array $storeTransfersIndexedByName): void
    {
        if ($moneyValueTransfer->getFkStore() !== null) {
            return;
        }

        $store = $moneyValueTransfer->getStore();

        if ($store === null || $store->getName() === null) {
            return;
        }

        $resolvedStore = $storeTransfersIndexedByName[$store->getName()] ?? null;

        if ($resolvedStore === null) {
            return;
        }

        $moneyValueTransfer->setFkStore($resolvedStore->getIdStoreOrFail());
        $moneyValueTransfer->setStore($resolvedStore);
    }
}
