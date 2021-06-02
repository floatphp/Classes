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

class Tokenizer
{
    /**
     * @access public
     * @param string $user
     * @param string $password
     * @return array
     */
    public static function get($user, $password) : array
    {
        $tokens = [];
        if ( Password::isStrong($password) ) {
            $secret = md5(microtime().rand());
            $encryption = new Encryption(trim("{{$user}}:{{$password}}"),$secret);
            $tokens = [
                'public' => $encryption->encrypt(),
                'secret' => $secret
            ];
        }
        return $tokens;
    }

    /**
     * @access public
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function range(int $min, int $max) : int
    {
        $range = $max - $min;
        if ( $range < 0 ) {
            return $min;
        }
        $log = log($range,2);
        $bytes = (int) ($log / 8) + 1;
        $bits = (int) $log + 1;
        $filter = (1 << $bits) - 1;
        do {
            $randomBytes = (string) openssl_random_pseudo_bytes($bytes);
            $rand = hexdec(bin2hex($randomBytes));
            $rand = $rand & $filter;
        } while ($rand >= $range);
        return $min + $rand;
    }

    /**
     * @access public
     * @param int $length
     * @param string $seeds
     * @return string
     */
    public static function generate(int $length = 32, $seeds = '') : string
    {
        $token = '';
        if ( empty($seeds) ) {
            $seeds  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $seeds .= 'abcdefghijklmnopqrstuvwxyz';
            $seeds .= '0123456789';
        }
        for ($i = 0; $i < $length; $i++) {
            $token .= $seeds[self::range(0,strlen($seeds))];
        }
        return $token;
    }

    /**
     * base64 encode
     *
     * @access public
     * @param string $data
     * @param int $loop
     * @return string
     */
    public static function base64(string $data = '', int $loop = 1) : string
    {
        $encode = base64_encode($data);
        for ($i = 1; $i < $loop; $i++) {
            $encode = base64_encode($encode);
        }
        return $encode;
    }

    /**
     * base64 decode
     *
     * @access public
     * @param string $data
     * @param int $loop
     * @return string
     */
    public static function unbase64(string $data = '', int $loop = 1) : string
    {
        $decode = base64_decode($data);
        for ($i = 1; $i < $loop; $i++) {
            $decode = base64_decode($decode);
        }
        return $decode;
    }

    /**
     * Get unique Id
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getUniqueId() : string
    {
        return md5(uniqid(time()));
    }
}
