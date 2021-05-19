<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Html Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Html;

use Twig\Loader\FilesystemLoader as Loader;
use Twig\Environment as Environment;
use Twig\TwigFunction as Module;

final class Template
{
    /**
     * @param string $path
     * @param array $settings
     * @return object
     */
    public static function getEnvironment($path, $settings = [])
    {
        return new Environment(new Loader($path), $settings);
    }

    /**
     * @param string $name
     * @param callable $function
     * @return object
     */
    public static function extend($name, $function)
    {
        return new Module($name, $function);
    }
}
