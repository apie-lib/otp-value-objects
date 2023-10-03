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
}
