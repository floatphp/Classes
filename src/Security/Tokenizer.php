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

/**
 * Built-in tokenizer class,
 * @uses JWT for external use is recommended.
 */
class Tokenizer
{
    /**
     * Get token.
     * 
     * @access public
     * @param string $user
     * @param string $pswd
     * @param string $prefix
     * @return array
     */
    public static function get(string $user, string $pswd, ?string $prefix = null) : array
    {
        $secret = self::getUniqueId();
        $token = trim("{user:{$user}}{pswd:{$pswd}}");
        $encryption = new Encryption($token, $secret);
        $encryption->setPrefix((string)$prefix);
        return [
            'public' => $encryption->encrypt(),
            'secret' => $secret
        ];
    }

    /**
     * Match token.
     * 
     * @access public
     * @param string $public
     * @param string $secret
     * @param string $prefix
     * @return mixed
     */
    public static function match(string $public, string $secret, ?string $prefix = null)
    {
        $pattern = '/{user:(.*?)}{pswd:(.*?)}/';
        $encryption = new Encryption($public, $secret);
        $encryption->setPrefix((string)$prefix);
        $access = $encryption->decrypt();
        $user = Stringify::match($pattern, $access, 1);
        $pswd = Stringify::match($pattern, $access, 2);
        if ( $user && $pswd ) {
            return [
                'username' => $user,
                'password' => $pswd
            ];
        }
        return false;
    }

    /**
     * Get range of numbers.
     * 
     * @access public
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function range(int $min = 5, int $max = 10) : int
    {
        $range = $max - $min;
        if ( $range < 0 ) {
            return $min;
        }
        $log = log($range, 2);
        $bytes = (int)($log / 8) + 1;
        $bits = (int)$log + 1;
        $filter = (1 << $bits) - 1;
        do {
            $randomBytes = (string)openssl_random_pseudo_bytes($bytes);
            $rand = hexdec(bin2hex($randomBytes));
            $rand = $rand & $filter;
        } while ($rand >= $range);
        return $min + $rand;
    }

    /**
     * Generate token.
     * 
     * @access public
     * @param int $length
     * @param bool $special
     * @return string
     */
    public static function generate(int $length = 16, bool $special = false) : string
    {
        $token = '';
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars .= 'abcdefghijklmnopqrstuvwxyz';
        $chars .= '0123456789';
        if ( $special ) {
            $chars .= '!#$%&()*+,-.:;<>?@[]^{}~';
        }
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[self::range(0, strlen($chars))];
        }
        return $token;
    }

    /**
     * base64 encode.
     *
     * @access public
     * @param string $data
     * @param int $loop
     * @return string
     */
    public static function base64(string $data, int $loop = 1) : string
    {
        $encode = base64_encode($data);
        $loop = ($loop > 10) ? 10 : $loop;
        for ($i = 1; $i < $loop; $i++) {
            $encode = base64_encode($encode);
        }
        return $encode;
    }

    /**
     * base64 decode.
     *
     * @access public
     * @param string $data
     * @param int $loop
     * @return string
     */
    public static function unbase64(string $data, int $loop = 1) : string
    {
        $decode = base64_decode($data);
        $loop = ($loop > 10) ? 10 : $loop;
        for ($i = 1; $i < $loop; $i++) {
            $decode = base64_decode($decode);
        }
        return $decode;
    }

    /**
     * Get unique Id.
     *
     * @access public
     * @return string
     */
    public static function getUniqueId() : string
    {
        return md5(
            uniqid((string)time())
        );
    }

    /**
     * Get UUID (4).
     *
     * @access public
     * @return string
     */
    public static function getUuid() : string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
