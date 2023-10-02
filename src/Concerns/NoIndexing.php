<?php
namespace Apie\OtpValueObjects\Concerns;

trait NoIndexing
{
    public static function noIndexing(): array
    {
        return [];
    }
}