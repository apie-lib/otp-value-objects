<?php
namespace Apie\OtpValueObjects;

use Apie\Core\ApieLib;
use Apie\Core\Attributes\FakeMethod;
use Apie\Core\Attributes\ProvideIndex;
use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\IsStringWithRegexValueObject;
use Apie\OtpValueObjects\Concerns\NoIndexing;
use chillerlan\QRCode\QRCode;
use OTPHP\TOTP;

#[FakeMethod('createRandom')]
#[ProvideIndex('noIndexing')]
class TOTPSecret implements StringValueObjectInterface
{
    use IsStringWithRegexValueObject;
    use NoIndexing;

    public static function createRandom(): self
    {
        $totp = TOTP::create(clock: ApieLib::getPsrClock());
        return new self($totp->getSecret());
    }

    public function createOTP(): OTP
    {
        return new OTP(TOTP::create($this->internal, clock: ApieLib::getPsrClock())->now());
    }

    public static function getRegularExpression(): string
    {
        return '/^[A-Z0-9]{103}$/';
    }

    public function getUrl(string $label): string
    {
        $tmp = TOTP::create($this->internal, clock: ApieLib::getPsrClock());
        $tmp->setLabel($label);
        return (new QRCode)->render($tmp->getProvisioningUri());
    }

    public function getQrCodeUri(string $label): string
    {
        $tmp = TOTP::create($this->internal, clock: ApieLib::getPsrClock());
        $tmp->setLabel($label);
        return $tmp->getQrCodeUri(
            'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
            '[DATA]'
        );
    }

    public function verify(OTP $otp): bool
    {
        // Use OTPHP library to generate a TOTP and compare it with the inputOTP
        $totp = TOTP::create($this->internal, clock: ApieLib::getPsrClock());
        return $totp->verify($otp->toNative());
    }
}
