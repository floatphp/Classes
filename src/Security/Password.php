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

final class Password
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
     * @return bool
     */
    public static function isStrong(string $password) : bool
    {
        $uppercase = Stringify::match('@[A-Z]@',$password);
        $lowercase = Stringify::match('@[a-z]@',$password);
        $number = Stringify::match('@[0-9]@',$password);
        $specialChars = Stringify::match('@[^\w]@',$password);

        if ( !$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8 ) {
            return false;
        }
        return false;
    }
}
