<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.2.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\Filesystem\Stringify;

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
    public static function generate(int $length = self::LENGTH, bool $special = true) : string
    {
        return Tokenizer::generate($length, $special);
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
        return password_verify($pswd, $hash);
    }

    /**
     * Get password hash.
     *
     * [BCRYPT: 2y].
     *
     * @access public
     * @param string $pswd
     * @param mixed $algo
     * @param array $options
     * @return mixed
     */
    public static function hash(string $pswd, $algo = '2y', array $options = [])
    {
        return password_hash($pswd, $algo, $options);
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

        if ( !$uppercase 
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
