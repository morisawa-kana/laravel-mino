<?php

namespace Morisawa\Auth\Encryption;

class Hmac
{
    public static string $HASH_HMAC;

    public static function initialize()
    {
        self::$HASH_HMAC = config('kaede.sign');
    }

    public static function generateSign($string): string
    {
        self::initialize();

        return $string.hash_hmac('sha3-256', $string, self::$HASH_HMAC);
    }

    public static function verifySign($originalData, $hashValue): bool
    {
        self::initialize();

        $calculatedHash = hash_hmac('sha3-256', $originalData, self::$HASH_HMAC);

        // hash_equals
        return hash_equals($hashValue, $calculatedHash);
    }
}
