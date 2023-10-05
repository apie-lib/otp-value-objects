<?php
namespace Apie\OtpValueObjects;

use Apie\Core\Attributes\ProvideIndex;
use ReflectionProperty;

/**
 * Use and extend this class if you want to enable 2FA action by telling the property of the OTP secret
 * and the label required to generate the QR Code.
 */
#[ProvideIndex('noIndexing')]
abstract class VerifyOTP extends OTP
{
    /**
     * @return ReflectionProperty
     */
    abstract public static function getOtpReference(): ReflectionProperty;

    abstract public static function getOtpLabel(): string;
}
