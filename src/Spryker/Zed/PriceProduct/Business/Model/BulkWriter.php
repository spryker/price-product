<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Model;

use Generated\Shared\Transfer\PriceProductTransfer;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProduct;

class BulkWriter extends Writer implements BulkWriterInterface
{
    /**
     * @var array<mixed>
     */
    protected $recordsToTouch = [];

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    public function createPriceForProduct(PriceProductTransfer $priceProductTransfer)
    {
        $priceProductTransfer = $this->setPriceType($priceProductTransfer);

        $this->loadPriceProductTransfer($priceProductTransfer);

        /** @var \Generated\Shared\Transfer\SpyPriceProductStoreEntityTransfer $persistedPriceProductTransfer */
        $persistedPriceProductTransfer = $this->savePriceProductEntity($priceProductTransfer, new SpyPriceProduct());

        if ($priceProductTransfer->getIdProduct()) {
            /** @var int $idProduct */
            $idProduct = $priceProductTransfer->requireIdProduct()->getIdProduct();
            $this->addRecordToTouch(static::TOUCH_PRODUCT, $idProduct);
        }

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer */
        $priceProductTransfer = $persistedPriceProductTransfer->getPriceProduct();

        return $priceProductTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return void
     */
    public function setPriceForProduct(PriceProductTransfer $priceProductTransfer)
    {
        $priceProductTransfer = $this->setPriceType($priceProductTransfer);

        $this->loadPriceProductTransfer($priceProductTransfer);

        /** @var int $idPriceProduct */
        $idPriceProduct = $priceProductTransfer->getIdPriceProduct();
        $priceProductEntity = $this->getPriceProductById($idPriceProduct);
        $this->savePriceProductEntity($priceProductTransfer, $priceProductEntity);

        if ($priceProductTransfer->getIdProduct()) {
            /** @var int $idProduct */
            $idProduct = $priceProductTransfer->getIdProduct();
            $this->addRecordToTouch(static::TOUCH_PRODUCT, $idProduct);
        }
    }

    /**
     * @param string $itemType
     * @param int $itemId
     *
     * @return void
     */
    protected function addRecordToTouch($itemType, $itemId)
    {
        $this->recordsToTouch[$itemType][] = $itemId;
    }

    /**
     * @return void
     */
    public function flush()
    {
        foreach ($this->recordsToTouch as $itemType => $itemIds) {
            $this->touchFacade->bulkTouchActive($itemType, $itemIds);
        }
        $this->recordsToTouch = [];
    }
}
