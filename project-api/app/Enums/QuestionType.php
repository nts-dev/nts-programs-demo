<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ShortAnswer()
 * @method static static TrueFalse()
 * @method static static MultiChoice()
 * @method static static Matching()
 * @method static static Numerical()
 * @method static static Essay()
 */
final class QuestionType extends Enum
{
    const ShortAnswer = 1;
    const TrueFalse =   2;
    const MultiChoice = 3;
    const Matching =    5;
    const Numerical =   8;
    const Essay =       10;
}
