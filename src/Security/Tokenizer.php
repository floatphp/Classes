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

final class Tokenizer
{
    /**
     * @access private
     * @var string $range
     */
    private const RANGE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    private const LENGTH = 32;
    private $range;

    /**
     * @param string $range
     * @return void
     */
    public function __construct(string $range = self::RANGE)
    {
        $this->range = $range;
    }

    /**
     * @access public
     * @param string $user
     * @param string $password
     * @param string $prefix
     * @return array
     */
    public function generateHash($user, $password)
    {
        $secret = md5(microtime().rand());
        $encryption = new Encryption("{$user}:{$password}",$secret);
        $encryption->setPrefix($prefix);
        return [
            'public' => $encryption->encrypt(),
            'secret' => $secret
        ];
    }

    /**
     * @access private
     * @param int $min
     * @param int $max
     * @return int
     */
    private function getFromRange(int $min, int $max) : int
    {
        $range = $max - $min;
        if ($range < 0) {
            return $min;
        }
        $log = log($range, 2);
        $bytes = (int)($log / 8) + 1;
        $bits = (int)$log + 1;
        $filter = (1 << $bits) - 1;
        do {
            $randomBytes = (string)openssl_random_pseudo_bytes($bytes);
            $rnd = hexdec(bin2hex($randomBytes));
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    /**
     * @access public
     * @param int $length
     * @return string
     */
    public function generate(int $length = self::LENGTH) : string
    {
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $this->range[$this->getFromRange(0, strlen($this->range))];
        }
        return $token;
    }

    /**
     * @access public
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function isValidPassword($password, $hash)
    {
        return password_verify($password,$hash);
    }
}
