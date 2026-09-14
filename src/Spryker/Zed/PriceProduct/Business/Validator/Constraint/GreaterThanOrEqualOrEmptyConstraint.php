<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Validator\Constraint;

use Symfony\Component\Validator\Constraints\AbstractComparison;

class GreaterThanOrEqualOrEmptyConstraint extends AbstractComparison
{
    protected const string DEFAULT_MESSAGE = 'This value should be equal or greater than {{ compared_value }} or empty.';

    public function __construct(mixed $value = null, mixed ...$arguments)
    {
        if (is_array($value) && !array_key_exists('message', $value)) {
            $value['message'] = static::DEFAULT_MESSAGE;
        }

        parent::__construct($value, ...$arguments);
    }

    public function getTargets(): string
    {
        return static::CLASS_CONSTRAINT;
    }
}
