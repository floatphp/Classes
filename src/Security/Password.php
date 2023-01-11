<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.0.1
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
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
     * Generate random password.
     *
     * @access public
     * @param int $length
     * @param bool $special
     * @return string
     */
    public static function generate($length = 12, $special = true) : string
    {
        return parent::generate($length, $special);
    }

    /**
     * @access public
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function isValid(string $password, string $hash) : bool
    {
        return password_verify($password, $hash);
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
     * @param string $password
     * @param int $length
     * @return bool
     */
    public static function isStrong($password = '', int $length = 8) : bool
    {
        if ( $length < 8 ) {
            $length = 8;
        }
        
        $uppercase = Stringify::match('@[A-Z]@', $password);
        $lowercase = Stringify::match('@[a-z]@', $password);
        $number    = Stringify::match('@[0-9]@', $password);
        $special   = Stringify::match('@[^\w]@', $password);

        if ( !$uppercase 
          || !$lowercase 
          || !$number 
          || !$special 
          || strlen($password) < $length 
        ) {
            return false;
        }
        return true;
    }
}
