<?php
namespace Apie\OtpValueObjects;

use Apie\Core\Attributes\FakeMethod;
use Apie\Core\Attributes\ProvideIndex;
use Apie\Core\Lists\StringHashmap;
use Apie\Core\ValueObjects\CompositeValueObject;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\OtpValueObjects\Concerns\NoIndexing;
use Apie\Serializer\Exceptions\ValidationException;
use chillerlan\QRCode\QRCode;
use OTPHP\HOTP;

#[FakeMethod('createRandom')]
#[ProvideIndex('noIndexing')]
class HOTPSecret implements ValueObjectInterface
{
    use CompositeValueObject;
    use NoIndexing;

    private string $secret;

    private int $counter;

    public function __construct(HOTP $hotp)
    {
        $this->secret = $hotp->getSecret();
        $this->counter = $hotp->getCounter();
    }

    public static function createRandom(): self
    {
        return new self(HOTP::create());
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getCounter(): string
    {
        return $this->counter;
    }

    public function getUrl(string $label): string
    {
        $tmp = HOTP::create($this->secret, $this->counter);
        $tmp->setLabel($label);
        return (new QRCode)->render($tmp->getProvisioningUri());
    }

    private function validateState(): void
    {
        $errors = [];
        if ($this->counter < 0) {
            $errors['counter'] = 'Counter should higher than or equal to 0';
        }
        if (!preg_match('/^[A-Z0-9]{103}$/', $this->secret)) {
            $errors['secret'] = 'Secret is not in valid format';
        }

        if (!empty($errors)) {
            throw new ValidationException(new StringHashmap($errors));
        }
    }

    public function createOTP(): OTP
    {
        return new OTP(HOTP::create($this->secret, $this->counter)->at($this->counter));
    }

    public function nextPassword(): self
    {
        $res = clone $this;
        $res->counter++;
        return $res;
    }

    public function verify(OTP $otp): bool
    {
        $hotp = HOTP::create($this->secret, $this->counter);
        return $hotp->verify($otp->toNative());
    }
}
