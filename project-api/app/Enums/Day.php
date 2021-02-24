<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Mon()
 * @method static static Tue()
 * @method static static Wed()
 * @method static static Thu()
 * @method static static Fri()
 * @method static static Sat()
 * @method static static Sun()
 */
final class Day extends Enum
{
    const Mon = 1;
    const Tue = 2;
    const Wed = 3;
    const Thu = 4;
    const Fri = 5;
    const Sat = 6;
    const Sun = 7;
}
