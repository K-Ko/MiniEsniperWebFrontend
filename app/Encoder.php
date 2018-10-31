<?php
/**
 *
 */
namespace App;

/**
 * Reverse encoder
 *
 * Based on https://github.com/HireUkraine1/ReverseEncoder
 */
abstract class Encoder
{
    /**
     * Encrypt a string
     *
     * @param [string] $data
     * @param [string] $secret
     * @return string
     */
    public static function encrypt($data, $secret)
    {
        $ivLen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLen);
        $rawData = openssl_encrypt($data, self::$cipher, $secret, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $rawData, $secret, true);
        return rtrim(base64_encode($iv . $hmac . $rawData), '=');
    }

    /**
     * Decrypt a string
     *
     * @param [string] $data
     * @param [string] $secret
     * @return string|false
     */
    public static function decrypt($data, $secret)
    {
        $ciphertext = base64_decode($data);
        $ivLen = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($ciphertext, 0, $ivLen);
        $hmac = substr($ciphertext, $ivLen, $sha2len = 32);
        $rawData = substr($ciphertext, $ivLen + $sha2len);
        $original_data = openssl_decrypt($rawData, self::$cipher, $secret, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $rawData, $secret, true);
        return hash_equals($hmac, $calcmac) ? $original_data : false;
    }

    /**
     * Cipher to use
     *
     * @var string
     */
    private static $cipher = "AES-128-CBC";
}
