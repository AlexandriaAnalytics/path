<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserStatus extends Enum
{
    const BLOCKED =   0;
    const OptionTwo =   1;
    const OptionThree = 2;
    const OptionFour = 3;

    public static function getOptions()
    {
        $options = [];
        foreach (self::getValues() as $value) {
            $options[$value] = $value;
        }
        return $options;
    }
}
