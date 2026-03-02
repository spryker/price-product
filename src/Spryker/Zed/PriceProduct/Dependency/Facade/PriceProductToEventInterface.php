<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Dependency\Facade;

use Spryker\Shared\Kernel\Transfer\TransferInterface;

interface PriceProductToEventInterface
{
    public function trigger(string $eventName, TransferInterface $transfer): void;
}
