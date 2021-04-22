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

class Hooks
{
  /**
   * @access protected
   * @var array $filters
   * @var array $mergedFilters
   * @var array $actions
   * @var array $currentFilter
   */
  protected $filters = [];
  protected $mergedFilters = [];
  protected $actions = [];
  protected $currentFilter = [];

  /**
   * @access public
   * @var array $shortcodeTags
   */
  public static $shortcodeTags = [];

  /**
   * @access private
   * @var int const PRIORITY
   */
  const PRIORITY = 50;

  /**
   * Prevent the object from being constructed
   */
  protected function __construct(){}

  /**
   * Prevent the object from being cloned
   */
  protected function __clone(){}

  /**
   * Prevent serialization
   */
  public function __wakeup(){}

  /**
   * Returns a Singleton instance of this class
   *
   * @param void
   * @return Object Hook
   */
  public static function getInstance()
  {
    static $instance;
    if ( null === $instance ) {
      $instance = new self();
    }
    return $instance;
  }

  /**
   * Add Hooks to function or method to a specific filter action
   *
   * @access public
   * @param string $tag
   * @param string|array $callableTodo
   * @param int $priority PRIORITY(50)
   * @param string $path null
   * @return true
   */
  public function addFilter($tag, $callableTodo, $priority = self::PRIORITY, $path = null)
  {
    $id = $this->filterUniqueId($callableTodo);
    $this->filters[$tag][$priority][$id] = [
      'callable' => $callableTodo,
      'path'     => is_string($path) ? $path : null
    ];
    unset($this->mergedFilters[$tag]);
    return true;
  }

  /**
   * Remove function from a specified filter hook
   *
   * @access public
   * @param string $tag
   * @param string|array $callableToRemove
   * @param int $priority PRIORITY(50)
   * @return bool
   */
  public function removeFilter($tag, $callableToRemove, $priority = self::PRIORITY)
  {
    $callableToRemove = $this->filterUniqueId($callableToRemove);
    if ( !isset($this->filters[$tag][$priority][$callableToRemove]) ) {
      return false;
    }
    unset($this->filters[$tag][$priority][$callableToRemove]);
    if ( empty($this->filters[$tag][$priority]) ) {
      unset($this->filters[$tag][$priority]);
    }
    unset($this->mergedFilters[$tag]);
    return true;
  }

  /**
   * Remove all of the hooks from a filter
   *
   * @access public
   * @param string $tag
   * @param int $priority false
   * @return bool
   */
  public function removeFilters($tag, $priority = false)
  {
    if ( isset($this->mergedFilters[$tag]) ) {
      unset($this->mergedFilters[$tag]);
    }
    if ( !isset($this->filters[$tag]) ) {
      return true;
    }
    if ( $priority !== false && isset($this->filters[$tag][$priority]) ) {
      unset($this->filters[$tag][$priority]);
    } else {
      unset($this->filters[$tag]);
    }
    return true;
  }

  /**
   * Check if any filter has been registered for the given hook
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function hasFilter($tag, $callableToCheck = false)
  {
    $has = isset($this->filters[$tag]);
    if ( $callableToCheck === false || !$has ) {
      return $has;
    }
    if ( !($id = $this->filterUniqueId($callableToCheck)) ) {
      return false;
    }
    foreach ( (array)array_keys($this->filters[$tag]) as $priority ) {
      if ( isset($this->filters[$tag][$priority][$id]) ) {
        return $priority;
      }
    }
    return false;
  }

  /**
   * Call the functions added to a filter hook
   *
   * @access public
   * @param string|array $tag
   * @param mixed $value
   * @return mixed
   */
  public function applyFilters($tag, $value)
  {
    $args = [];
    // Do 'all' actions first
    if ( isset($this->filters['all']) ) {
      $this->currentFilter[] = $tag;
      $args = func_get_args();
      $this->callHooks($args);
    }
    if (!isset($this->filters[$tag])) {
      if (isset($this->filters['all'])) {
        array_pop($this->currentFilter);
      }
      return $value;
    }
    if (!isset($this->filters['all'])) {
      $this->currentFilter[] = $tag;
    }
    // Sort
    if ( !isset($this->mergedFilters[$tag]) ) {
      ksort($this->filters[$tag]);
      $this->mergedFilters[$tag] = true;
    }
    reset($this->filters[$tag]);
    if ( empty($args) ) {
      $args = func_get_args();
    }
    array_shift($args);
    do {
      foreach ( (array)current($this->filters[$tag]) as $current ) {
        if ( $current['callable'] !== null ) {
          if ( $current['path'] !== null ) {
            include_once $current['path'];
          }
          $args[0] = $value;
          $value = call_user_func_array($current['callable'], $args);
        }
      }
    } while ( next($this->filters[$tag]) !== false );
    array_pop($this->currentFilter);
    return $value;
  }

  /**
   * Execute functions hooked on a specific filter hook, specifying arguments in an array
   *
   * @access public
   * @param string $tag
   * @param array $args
   * @return mixed
   */
  public function applyFiltersArray($tag, $args)
  {
    // Do 'all' actions first
    if ( isset($this->filters['all']) ) {
      $this->currentFilter[] = $tag;
      $allArgs = func_get_args();
      $this->callHooks($allArgs);
    }
    if ( !isset($this->filters[$tag]) ) {
      if (isset($this->filters['all'])) {
        array_pop($this->currentFilter);
      }
      return $args[0];
    }
    if ( !isset($this->filters['all']) ) {
      $this->currentFilter[] = $tag;
    }
    // Sort
    if (!isset($this->mergedFilters[$tag])) {
      ksort($this->filters[$tag]);
      $this->mergedFilters[$tag] = true;
    }
    reset($this->filters[$tag]);
    do {
      foreach ( (array)current($this->filters[$tag]) as $current ) {
        if ( $current['callable'] !== null ) {
          if ( $current['path'] !== null ) {
            include_once $current['path'];
          }
          $args[0] = call_user_func_array($current['callable'], $args);
        }
      }
    } while ( next($this->filters[$tag]) !== false );
    array_pop($this->currentFilter);
    return $args[0];
  }

  /**
   * Hooks a function on to a specific action
   *
   * @access public
   * @param string $tag
   * @param array $args
   * @return mixed
   */
  public function addAction($tag, $callableTodo, $priority = self::PRIORITY, $path = null)
  {
    return $this->addFilter($tag, $callableTodo, $priority, $path);
  }

  /**
   * Check if any action has been registered for a hook.
   *
   * @access public
   * @param string $tag
   * @param array $args
   * @return mixed
   */
  public function hasAction($tag, $callableToCheck = false)
  {
    return $this->hasFilter($tag, $callableToCheck);
  }

  /**
   * Removes a function from a specified action hook
   *
   * @access public
   * @param string $tag
   * @param array $args
   * @return mixed
   */
  public function remove_action($tag, $callableToRemove, $priority = self::PRIORITY)
  {
    return $this->removeFilter($tag, $callableToRemove, $priority);
  }

  /**
   * Remove all of the hooks from an action
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function removeAllActions($tag, $priority = false)
  {
    return $this->removeFilters($tag, $priority);
  }

  /**
   * Execute functions hooked on a specific action hook
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function doAction($tag, $arg = '')
  {
    if ( !is_array($this->actions) ) {
      $this->actions = [];
    }
    if ( !isset($this->actions[$tag]) ) {
      $this->actions[$tag] = 1;
    } else {
      ++$this->actions[$tag];
    }
    // Do 'all' actions first
    if ( isset($this->filters['all']) ) {
      $this->currentFilter[] = $tag;
      $allArgs = func_get_args();
      $this->callHooks($allArgs);
    }
    if ( !isset($this->filters[$tag]) ) {
      if (isset($this->filters['all'])) {
        array_pop($this->currentFilter);
      }
      return false;
    }
    if (!isset($this->filters['all'])) {
      $this->currentFilter[] = $tag;
    }
    $args = [];
    if (
        is_array($arg)
        &&
        isset($arg[0])
        &&
        is_object($arg[0])
        &&
        1 == count($arg)
    ) {
      $args[] =& $arg[0];
    } else {
      $args[] = $arg;
    }
    $numArgs = func_num_args();
    for ($a = 2; $a < $numArgs; $a++) {
      $args[] = func_get_arg($a);
    }
    // Sort
    if ( !isset($this->mergedFilters[$tag]) ) {
      ksort($this->filters[$tag]);
      $this->mergedFilters[$tag] = true;
    }
    reset($this->filters[$tag]);
    do {
      foreach ( (array)current($this->filters[$tag]) as $current ) {
        if ( $current['callable'] !== null ) {
          if ($current['path'] !== null ) {
            include_once $current['path'];
          }
          call_user_func_array($current['callable'], $args);
        }
      }
    } while ( next($this->filters[$tag]) !== false );
    array_pop($this->currentFilter);
    return true;
  }

  /**
   * Execute functions hooked on a specific action hook, specifying arguments in an array
   *
   * @access public
   * @param string $tag
   * @param array $args
   * @return mixed
   */
  public function doActionArray($tag, $args)
  {
    if ( !is_array($this->actions) ) {
      $this->actions = [];
    }
    if ( !isset($this->actions[$tag]) ) {
      $this->actions[$tag] = 1;
    } else {
      ++ $this->actions[$tag];
    }
    // Do 'all' actions first
    if ( isset($this->filters['all']) ) {
      $this->currentFilter[] = $tag;
      $allArgs = func_get_args();
      $this->callHooks($allArgs);
    }
    if ( !isset($this->filters[$tag]) ) {
      if (isset($this->filters['all'])) {
        array_pop($this->currentFilter);
      }
      return false;
    }
    if ( !isset($this->filters['all']) ) {
      $this->currentFilter[] = $tag;
    }
    // Sort
    if ( !isset($mergedFilters[$tag]) ) {
      ksort($this->filters[$tag]);
      $mergedFilters[$tag] = true;
    }
    reset($this->filters[$tag]);
    do {
      foreach ( (array)current($this->filters[$tag]) as $current ) {
        if ( $current['callable'] !== null ) {
          if ( $current['path'] !== null ) {
            include_once $current['path'];
          }
          call_user_func_array($current['callable'], $args);
        }
      }
    } while ( next($this->filters[$tag]) !== false );
    array_pop($this->currentFilter);
    return true;
  }

  /**
   * Retrieve the number of times an action has fired
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function didAction($tag)
  {
    if ( !is_array($this->actions) || !isset($this->actions[$tag]) ) {
      return 0;
    }
    return $this->actions[$tag];
  }

  /**
   * Retrieve the name of the current filter or action
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function currentFilter()
  {
    return end($this->currentFilter);
  }

  /**
   * Build Unique ID for storage and retrieval
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  private function filterUniqueId($function)
  {
    if ( is_string($function) ) {
      return $function;
    }
    if ( is_object($function) ) {
      // Closures are currently implemented as objects
      $function = [$function,''];
    } else {
      $function = (array)$function;
    }
    if ( is_object($function[0]) ) {
      // Object Class Calling
      return spl_object_hash($function[0]) . $function[1];
    }
    if ( is_string($function[0]) ) {
      // Static Calling
      return $function[0] . $function[1];
    }
    return false;
  }

  /**
   * Call All Hooks
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function callHooks($args)
  {
    reset($this->filters['all']);
    do {
      foreach ((array)current($this->filters['all']) as $current) {
        if ( $current['callable'] !== null ) {
          if ( $current['path'] !== null ) {
            include_once $current['path'];
          }
          call_user_func_array($current['callable'], $args);
        }
      }
    } while ( next($this->filters['all']) !== false );
  }

  /**
   * Breaking changes of callHooks method
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function callHooksAfter($args)
  {
    $this->callHooks($args);
  }

  /**
   * Add hook for shortcode tag
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function addShortcode($tag, $func)
  {
    if ( is_callable($func) ) {
      self::$shortcodeTags[$tag] = $func;
      return true;
    }
    return false;
  }

  /**
   * Removes hook for shortcode
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function removeShortcode($tag)
  {
    if ( isset(self::$shortcodeTags[$tag]) ) {
      unset(self::$shortcodeTags[$tag]);
      return true;
    }
    return false;
  }

  /**
   * Clears all of the shortcode tags
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function removeAllShortcodes()
  {
    self::$shortcodeTags = [];
    return true;
  }

  /**
   * Whether a registered shortcode exists named $tag
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function shortcodeExists($tag)
  {
    return array_key_exists($tag, self::$shortcodeTags);
  }

  /**
   * Whether the passed content contains the specified shortcode
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function hasShortcode($content, $tag)
  {
    if ( false === strpos($content, '[') ) {
      return false;
    }
    if ( $this->shortcodeExists($tag) ) {
      preg_match_all('/' . $this->getShortcodeRegex() . '/s', $content, $matches, PREG_SET_ORDER);
      if (empty($matches)) {
        return false;
      }
      foreach ($matches as $shortcode) {
        if ($tag === $shortcode[2]) {
          return true;
        }
        if (!empty($shortcode[5]) && $this->hasShortcode($shortcode[5], $tag)) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Search content for shortcodes and filter shortcodes through their hooks
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function doShortcode($content)
  {
    if ( empty(self::$shortcodeTags) || !is_array(self::$shortcodeTags) ) {
      return $content;
    }
    $pattern = $this->getShortcodeRegex();
    return preg_replace_callback("/$pattern/s", [$this,'doShortcodeTag'], $content);
  }

  /**
   * Retrieve the shortcode regular expression for searching
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function getShortcodeRegex()
  {
    $tagnames = array_keys(self::$shortcodeTags);
    $tagregexp = implode('|', array_map('preg_quote', $tagnames));
    return
      '\\['
      . '(\\[?)'
      . "($tagregexp)"
      . '(?![\\w-])'
      . '('
      . '[^\\]\\/]*'
      . '(?:'
      . '\\/(?!\\])'
      . '[^\\]\\/]*'
      . ')*?'
      . ')'
      . '(?:'
      . '(\\/)'
      . '\\]'
      . '|'
      . '\\]'
      . '(?:'
      . '('
      . '[^\\[]*+'
      . '(?:'
      . '\\[(?!\\/\\2\\])'
      . '[^\\[]*+'
      . ')*+'
      . ')'
      . '\\[\\/\\2\\]'
      . ')?'
      . ')'
      . '(\\]?)';
  }

  /**
   * Regular Expression callable for doShortcode() for calling shortcode hook
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  private function doShortcodeTag($m)
  {
    // allow [[foo]] syntax for escaping a tag
    if ( $m[1] == '[' && $m[6] == ']' ) {
      return substr($m[0], 1, -1);
    }
    $tag = $m[2];
    $attr = $this->shortcodeParseAtts($m[3]);
    // enclosing tag - extra parameter
    if ( isset($m[5]) ) {
      return $m[1] . call_user_func(self::$shortcodeTags[$tag], $attr, $m[5], $tag) . $m[6];
    }
    // self-closing tag
    return $m[1] . call_user_func(self::$shortcodeTags[$tag], $attr, null, $tag) . $m[6];
  }

  /**
   * Retrieve all attributes from the shortcodes tag
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function shortcodeParseAtts($text)
  {
    $atts = [];
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
    if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
      foreach ($match as $m) {
        if (!empty($m[1])) {
          $atts[strtolower($m[1])] = stripcslashes($m[2]);
        } elseif (!empty($m[3])) {
          $atts[strtolower($m[3])] = stripcslashes($m[4]);
        } elseif (!empty($m[5])) {
          $atts[strtolower($m[5])] = stripcslashes($m[6]);
        } elseif (isset($m[7]) && $m[7] !== '') {
          $atts[] = stripcslashes($m[7]);
        } elseif (isset($m[8])) {
          $atts[] = stripcslashes($m[8]);
        }
      }
    } else {
      $atts = ltrim($text);
    }
    return $atts;
  }

  /**
   * Combine user attributes with known attributes and fill in defaults when needed
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function shortcodeAtts($pairs, $atts, $shortcode = '')
  {
    $atts = (array)$atts;
    $out = [];
    foreach ($pairs as $name => $default) {
      if (array_key_exists($name, $atts)) {
        $out[$name] = $atts[$name];
      } else {
        $out[$name] = $default;
      }
    }
    if ($shortcode) {
      $out = $this->applyFilters(
        [
          $this,
          "shortcodeAtts_{$shortcode}"
        ], $out, $pairs, $atts
      );
    }
    return $out;
  }

  /**
   * Remove all shortcode tags from the given content
   *
   * @access public
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  public function stripShortcodes($content)
  {
    if ( empty(self::$shortcodeTags) || !is_array(self::$shortcodeTags) ) {
      return $content;
    }
    $pattern = $this->getShortcodeRegex();
    return preg_replace_callback(
      "/$pattern/s",[$this,'stripShortcodeTag'],$content
    );
  }

  /**
   * Strip shortcode by tag
   *
   * @access private
   * @param string $tag
   * @param string $callableToCheck false
   * @return mixed
   */
  private function stripShortcodeTag($m)
  {
    if ( $m[1] == '[' && $m[6] == ']' ) {
      return substr($m[0], 1, -1);
    }
    return $m[1] . $m[6];
  }
}
