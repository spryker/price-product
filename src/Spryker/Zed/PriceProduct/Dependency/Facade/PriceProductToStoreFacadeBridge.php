<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Dependency\Facade;

class PriceProductToStoreFacadeBridge implements PriceProductToStoreFacadeInterface
{
    /**
     * @var \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @param \Spryker\Zed\Store\Business\StoreFacadeInterface $storeFacade
     */
    public function __construct($storeFacade)
    {
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getStoreByName($storeName)
    {
        return $this->storeFacade->getStoreByName($storeName);
    }

    /**
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getCurrentStore()
    {
        return $this->storeFacade->getCurrentStore();
    }

    /**
     * @param int $idStore
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getStoreById($idStore)
    {
        return $this->storeFacade->getStoreById($idStore);
    }

    /**
     * @param array<string> $storeNames
     *
     * @return array<\Generated\Shared\Transfer\StoreTransfer>
     */
    public function getStoreTransfersByStoreNames(array $storeNames): array
    {
        return $this->storeFacade->getStoreTransfersByStoreNames($storeNames);
    }
}
