<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Server Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\TypeCheck;

final class System
{
	/**
	 * Memory exceeded
	 *
	 * @access public
	 * @param float $percent
	 * @return bool
	 */
	public static function isMemoryOut($percent = 0.9)
	{
		$limit = self::getMemoryLimit() * $percent;
		$current = self::getMemoryUsage();
		if ( $current >= $limit ) {
			return true;
		}
		return false;
	}

	/**
	 * Get memory limit
	 *
	 * @access public
	 * @param void
	 * @return int
	 */
	public static function getMemoryLimit()
	{
		if ( TypeCheck::isFunction('ini_get') ) {
			$limit = self::getIni('memory_limit');
			if ( Stringify::contains(Stringify::lowercase($limit), 'g') ) {
				$limit = intval($limit) * 1024;
				$limit = "{$limit}M";
			}
		} else {
			// Default
			$limit = '128M';
		}
		if ( !$limit || $limit === -1 ) {
			// Unlimited
			$limit = '32000M';
		}
		return intval($limit) * 1024 * 1024;
	}

	/**
	 * Get memory usage
	 *
	 * @access public
	 * @param void
	 * @return int
	 */
	public static function getMemoryUsage($real = true)
	{
		return memory_get_usage($real);
	}

    /**
     * Get OS
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getOs()
    {
    	return strtolower(PHP_OS);
    }

    /**
     * Get OS var
     *
     * @access public
     * @param string $var
     * @return string
     */
    public static function getOsVar($var = '')
    {
        $var = strtolower($var);
        switch ( self::getOs() ) {
            case 'freebsd':
            case 'netbsd':
            case 'solaris':
            case 'sunos':
            case 'darwin':
                switch ($var) {
                    case 'conf':
                        $var = '/sbin/ifconfig';
                        break;
                    case 'mac':
                        $var = 'ether';
                        break;
                    case 'ip':
                        $var = 'inet ';
                        break;
                }
                break;
            case 'linux':
                switch ($var) {
                    case 'conf':
                        $var = '/sbin/ifconfig';
                        break;
                    case 'mac':
                        $var = 'HWaddr';
                        break;
                    case 'ip':
                        $var = 'inet addr:';
                        break;
                }
                break;
        }
        return $var;
    }

    /**
     * Set ini
     *
     * @access public
     * @param string|array $option
     * @param string $value
     * @return mixed
     */
    public static function setIni($option, $value)
    {
        if ( TypeCheck::isArray($option) ) {
            $temp = [];
            foreach ($option as $key => $value) {
                $temp = ini_set($key,(string)$value);
            }
            return $temp;
        }
        return ini_set($option,(string)$value);
    }

    /**
     * Get ini
     *
     * @access public
     * @param string $option
     * @return mixed
     */
    public static function getIni(string $option)
    {
        return ini_get($option);
    }

    /**
     * Set time limit
     *
     * @access public
     * @param int $seconds
     * @param string $value
     * @return bool
     */
    public static function setTimeLimit($seconds = 30) : bool
    {
        return set_time_limit((int)$seconds);
    }

    /**
     * Set memory limit
     *
     * @access public
     * @param int|string $value
     * @return mixed
     */
    public static function setMemoryLimit($value = '128M')
    {
        return self::setIni('memory_limit',$value);
    }
}
