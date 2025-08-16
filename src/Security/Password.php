<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\Filesystem\Stringify;

/**
 * Advanced password manipulation.
 */
final class Password
{
    /**
     * @access private
     * @var int LENGTH, Password length
     */
    private const LENGTH = 8;

    /**
     * Generate password.
     *
     * @access public
     * @param int $length
     * @param bool $special
     * @return string
     */
    public static function generate(int $length = self::LENGTH, bool $special = false) : string
    {
        // Input validation
        if ( $length < 1 ) {
            throw new \InvalidArgumentException('Password length must be at least 1');
        }
        if ( $length > 1024 ) {
            throw new \InvalidArgumentException('Password length too large (max: 1024)');
        }

        $token = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars .= 'abcdefghijklmnopqrstuvwxyz';
        $chars .= '0123456789';
        if ( $special ) {
            $chars .= '!#$%&()*+,-.:;<>?@[]^{}~';
        }

        $maxIndex = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[Tokenizer::range(0, $maxIndex)];
        }
        return $token;
    }

    /**
     * Check whether password is valid against hash.
     * 
     * @access public
     * @param string $pswd
     * @param string $hash
     * @return bool
     */
    public static function isValid(string $pswd, string $hash) : bool
    {
        // Input validation
        if ( trim($pswd) === '' ) {
            return false;
        }
        if ( trim($hash) === '' ) {
            return false;
        }

        return password_verify($pswd, $hash);
    }

    /**
     * Get password hash.
     *
     * [BCRYPT: '2y'].
     *
     * @access public
     * @param string $pswd
     * @param mixed $algo
     * @param array $options
     * @return string
     */
    public static function hash(string $pswd, $algo = '2y', array $options = []) : string
    {
        // Input validation
        if ( trim($pswd) === '' ) {
            throw new \InvalidArgumentException('Password cannot be empty');
        }

        try {
            $hash = password_hash($pswd, $algo, $options);

            // Check if hashing failed
            if ( $hash === false ) {
                throw new \RuntimeException('Password hashing failed - possibly invalid algorithm or options');
            }

            return $hash;

        } catch (\Exception $e) {
            if ( $e instanceof \InvalidArgumentException || $e instanceof \RuntimeException ) {
                throw $e;
            }
            throw new \RuntimeException('Password hashing failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check password is strong.
     * 
     * @access public
     * @param string $pswd
     * @param int $length
     * @return bool
     */
    public static function isStrong(string $pswd, int $length = self::LENGTH) : bool
    {
        // Input validation
        if ( trim($pswd) === '' ) {
            return false;
        }

        // Edge case handling for extreme length values
        if ( $length < 1 ) {
            $length = self::LENGTH;
        }
        if ( $length > 1024 ) {
            $length = 1024;
        }

        if ( $length < self::LENGTH ) {
            $length = self::LENGTH;
        }

        Stringify::match('@[A-Z]@', $pswd, $matches, -1);
        $uppercase = $matches;

        Stringify::match('@[a-z]@', $pswd, $matches, -1);
        $lowercase = $matches;

        Stringify::match('@[0-9]@', $pswd, $matches, -1);
        $number = $matches;

        Stringify::match('@[^\w]@', $pswd, $matches, -1);
        $special = $matches;

        if (
            !$uppercase
            || !$lowercase
            || !$number
            || !$special
            || strlen($pswd) < $length
        ) {
            return false;
        }

        return true;
    }
}
