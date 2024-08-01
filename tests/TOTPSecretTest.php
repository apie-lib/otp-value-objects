<?php
namespace Apie\Tests\OtpValueObjects;

use Apie\Core\ApieLib;
use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\OtpValueObjects\TOTPSecret;
use Beste\Clock\FrozenClock;
use Beste\Clock\SystemClock;
use PHPUnit\Framework\TestCase;

class TOTPSecretTest extends TestCase
{
    use TestWithFaker;
    /**
     * @test
     */
    public function it_can_create_and_verify_an_otp()
    {
        $clock = FrozenClock::withNowFrom(SystemClock::create());
        ApieLib::setPsrClock($clock);
        $testItem = TOTPSecret::createRandom();
        $otp = $testItem->createOTP();
        $this->assertTrue($testItem->verify($otp));
        $clock->setTo($clock->now()->modify('+5 minutes'));
        $this->assertFalse($testItem->verify($otp));
    }

    /**
     * @test
     */
    public function it_can_fake_totp_secrets()
    {
        $clock = FrozenClock::withNowFrom(SystemClock::create());
        ApieLib::setPsrClock($clock);
        $this->runFakerTest(
            TOTPSecret::class,
            function (TOTPSecret $instance) {
                $otp = $instance->createOTP();
                $this->assertTrue($instance->verify($otp));
            }
        );
    }

    /**
     * @test
     */
    public function it_can_provide_a_url_from_qrserver()
    {
        $clock = FrozenClock::withNowFrom(SystemClock::create());
        ApieLib::setPsrClock($clock);
        $secret = str_repeat('A', 103);
        $testItem = new TOTPSecret($secret);
        $this->assertEquals(
            'https://api.qrserver.com/v1/create-qr-code/?data=otpauth%3A%2F%2Ftotp%2FTestcase%3Fsecret%3D' . $secret . '&size=300x300&ecc=M',
            $testItem->getQrCodeUri('Testcase')
        );
    }

    /**
     * @test
     */
    public function it_can_provide_a_base64_image()
    {
        $clock = FrozenClock::withNowFrom(SystemClock::create());
        ApieLib::setPsrClock($clock);
        $secret = str_repeat('A', 103);
        $testItem = new TOTPSecret($secret);
        $actual = $testItem->getUrl('Testcase');
        $this->assertStringStartsWith(
            'data:image/svg+xml;base64,',
            $actual
        );
    }
}
