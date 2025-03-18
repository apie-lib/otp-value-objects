<?php
namespace Apie\OtpValueObjects;

use Apie\Core\Attributes\ProvideIndex;
use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\IsStringWithRegexValueObject;
use Apie\OtpValueObjects\Concerns\NoIndexing;

#[ProvideIndex('noIndexing')]
class OTP implements StringValueObjectInterface
{
    use IsStringWithRegexValueObject;
    use NoIndexing;

    public static function getRegularExpression(): string
    {
        return '/^[\d]{6,8}$/';
    }
}
