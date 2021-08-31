<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Security Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\Filesystem\Stringify;

final class Password extends Tokenizer
{
    /**
     * @access public
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function isValid(string $password, string $hash) : bool
    {
        return password_verify($password,$hash);
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
        return password_hash($password,$algo,$options);
    }

    /**
     * @access public
     * @param string $password
     * @return bool
     */
    public static function isStrong(string $password) : bool
    {
        $uppercase = Stringify::match('@[A-Z]@',$password);
        $lowercase = Stringify::match('@[a-z]@',$password);
        $number    = Stringify::match('@[0-9]@',$password);
        $special   = Stringify::match('@[^\w]@',$password);

        if ( !$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8 ) {
            return false;
        }
        return true;
    }
}
