<?php
namespace Apie\Tests\OtpValueObjects;

use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\OtpValueObjects\HOTPSecret;
use Apie\Serializer\Exceptions\ValidationException;
use OTPHP\HOTP;
use PHPUnit\Framework\TestCase;

class HOTPSecretTest extends TestCase
{
    use TestWithFaker;
    /**
     * @test
     */
    public function it_can_create_and_verify_an_otp()
    {
        $testItem = HOTPSecret::createRandom();
        $otp = $testItem->createOTP();
        $this->assertTrue($testItem->verify($otp));
        $testItem = $testItem->nextPassword();
        $newOtp = $testItem->createOTP();
        $this->assertNotEquals($otp->toNative(), $newOtp->toNative());
        $this->assertTrue($testItem->verify($newOtp));
    }

    /**
     * @test
     * @dataProvider invalidProvider
     */
    public function it_can_validate_input(array $expected, array $input)
    {
        try {
            HOTPSecret::fromNative($input);
            $this->fail('fromNative should have thrown an exception');
        } catch (ValidationException $validationException) {
            $errors = $validationException->getErrors()->toArray();
            $this->assertEquals($expected, $errors);
        }
    }

    public function invalidProvider()
    {
        $validSecret = HOTPSecret::createRandom()->getSecret();
        yield 'empty array' => [
            [
                'counter' => 'Type "(missing value)" is not expected, expected int',
                'secret' => 'Type "(missing value)" is not expected, expected string'
            ],
            []
        ];
        yield 'counter negative' => [
            [
                'counter' => 'Counter should higher than or equal to 0'
            ],
            [
                'counter' => -1,
                'secret' => $validSecret
            ]
        ];
        yield 'secret invalid' => [
            [
                'secret' => 'Secret is not in valid format'
            ],
            [
                'counter' => 1,
                'secret' => 'invalid'
            ]
        ];
        yield 'both values are incorrect' => [
            [
                'counter' => 'Counter should higher than or equal to 0',
                'secret' => 'Secret is not in valid format'
            ],
            [
                'counter' => -1,
                'secret' => 'invalid'
            ]
        ];
    }

    /**
     * @test
     */
    public function it_can_fake_hotp_secrets()
    {
        $this->runFakerTest(
            HOTPSecret::class,
            function (HOTPSecret $instance) {
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
        $tmp = HOTP::create($secret, 0);
        $testItem = new HOTPSecret($tmp);
        $this->assertEquals(
            'https://api.qrserver.com/v1/create-qr-code/?data=otpauth%3A%2F%2Fhotp%2FTestcase%3Fcounter%3D0%26secret%3D' . $secret . '&size=300x300&ecc=M',
            $testItem->getQrCodeUri('Testcase')
        );
    }

    /**
     * @test
     */
    public function it_can_provide_a_base64_image()
    {
        $secret = str_repeat('A', 103);
        $tmp = HOTP::create($secret, 0);
        $testItem = new HOTPSecret($tmp);
        $actual = $testItem->getUrl('Testcase');
        $this->assertStringStartsWith(
            'data:image/svg+xml;base64,',
            $actual
        );
    }
}
