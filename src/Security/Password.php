<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.1.1
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\Filesystem\Stringify;

final class Password extends Tokenizer
{
    /**
     * Generate password.
     *
     * @access public
     * @param int $length
     * @param bool $special
     * @return string
     */
    public static function generate(int $length = 8, bool $special = true) : string
    {
        return parent::generate($length, $special);
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
     * @access public
     * @param string $password
     * @param string $algo
     * @param string $options
     * @return mixed
     */
    public static function hash(string $password, string $algo = PASSWORD_BCRYPT, array $options = [])
    {
        return password_hash($password, $algo, $options);
    }

    /**
     * Check password is strong.
     * 
     * @access public
     * @param string $pswd
     * @param int $length
     * @return bool
     */
    public static function isStrong(string $pswd, int $length = 8) : bool
    {
        if ( $length < 8 ) {
            $length = 8;
        }
        
        $uppercase = Stringify::match('@[A-Z]@', $pswd);
        $lowercase = Stringify::match('@[a-z]@', $pswd);
        $number    = Stringify::match('@[0-9]@', $pswd);
        $special   = Stringify::match('@[^\w]@', $pswd);

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
