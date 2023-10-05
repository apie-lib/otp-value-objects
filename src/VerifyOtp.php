<?php
namespace Apie\OtpValueObjects;

use Apie\Core\Attributes\ProvideIndex;
use ReflectionProperty;

#[ProvideIndex('noIndexing')]
abstract class VerifyOtp extends OTP
{
    /**
     * @return ReflectionProperty
     */
    abstract public static function getOtpReference(): ReflectionProperty;

    abstract public static function getOtpLabel(): string;
}
