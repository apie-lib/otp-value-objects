<?php
namespace Apie\Tests\OtpValueObjects;

use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\OtpValueObjects\HOTPSecret;
use Apie\Serializer\Exceptions\ValidationException;
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
            var_dump($input);
            HOTPSecret::fromNative($input);
            $this->fail('fromNative should have thrown an exception');
        } catch (ValidationException $validationException) {
            $errors = $validationException->getErrors()->toArray();
            $this->assertEquals($expected, $errors);
        }
    }

    public function invalidProvider()
    {
        $valid = HOTPSecret::createRandom()->toNative();
        yield 'empty array' => [['counter' => 'Type "(missing value)" is not expected, expected int', 'secret' => 'Type "(missing value)" is not expected, expected string'], []];
        yield 'counter negative' => [['counter' => 'Counter should higher than or equal to 0'], ['count' => -1, 'secret' => $valid]];
        yield 'secret invalid' => [['secret' => 'Secret is not in valid format'], ['count' => 1, 'secret' => $valid]];
        yield 'merging together gives more results then the timeline ' =>[
            ['counter' => 'Counter should higher than or equal to 0', 'secret' => 'Secret is not in valid format'],
            ['counter' => -1, 'secret' => $valid]];
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
}