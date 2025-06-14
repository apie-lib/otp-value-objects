<?php
namespace Apie\OtpValueObjects;

use Apie\Core\Attributes\Description;
use Apie\Core\Attributes\ProvideIndex;
use Apie\Core\Entities\EntityInterface;
use ReflectionProperty;

/**
 * Use and extend this class if you want to enable 2FA action by telling the property of the OTP secret
 * and the label required to generate the QR Code.
 */
#[ProvideIndex('noIndexing')]
#[Description('One time password, used in combination with HOTP or TOTP')]
abstract class VerifyOTP extends OTP
{
    /**
     * @return ReflectionProperty
     */
    abstract public static function getOtpReference(): ReflectionProperty;

    abstract public static function getOtpLabel(EntityInterface $entity): string;
}
