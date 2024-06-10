<?php
namespace Apie\Tests\OtpValueObjects;

use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\OtpValueObjects\TOTPSecret;
use PHPUnit\Framework\TestCase;

class TOTPSecretTest extends TestCase
{
    use TestWithFaker;
    /**
     * @test
     */
    public function it_can_create_and_verify_an_otp()
    {
        $testItem = TOTPSecret::createRandom();
        $otp = $testItem->createOTP();
        $this->assertTrue($testItem->verify($otp));
    }

    /**
     * @test
     */
    public function it_can_fake_totp_secrets()
    {
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
        $secret = str_repeat('A', 103);
        $testItem = new TOTPSecret($secret);
        $file = __DIR__ . '/../fixtures/totp.txt';
        $actual = $testItem->getUrl('Testcase');
        // file_put_contents($file, $actual);
        $this->assertEquals(
            file_get_contents($file),
            $actual
        );
    }
}
