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

use FloatPHP\Classes\Filesystem\Stringify;
use FloatPHP\Classes\Filesystem\TypeCheck;
use FloatPHP\Classes\Filesystem\Arrayify;
use FloatPHP\Classes\Http\Request;
use FloatPHP\Classes\Http\Session;

class Form
{
	/**
	 * @access private
	 * @var array $form
	 * @var string $token
	 * @var array $inputs
	 * @var array $systemInputs
	 * @var bool $hasSubmit
	 */
	private $form = [];
	private $token = '';
	private $inputs = [];
	private $systemInputs = [];
	private $hasSubmit = false;

	/**
	 * @param array $args
	 * @param string $action
	 */
	function __construct($args = false, $action = '')
	{
		// Default form attributes
		$defaults = [
			'action'       => $action,
			'method'       => 'post',
			'enctype'      => 'application/x-www-form-urlencoded',
			'class'        => [],
			'id'           => '',
			'novalidate'   => false,
			'antispam'     => true,
			'token'        => true,
			'form-element' => true,
			'submit'       => true,
			'submit-text'  => 'Submit',
			'submit-class' => 'btn btn-primary'
		];

		// Merge with arguments, if present
		if ( $args ) {
			$settings = Arrayify::merge($defaults,$args);
		} else {
			$settings = $defaults;
		}

		// Save each option
		foreach ( $settings as $key => $val ) {
			if ( !$this->setAttr($key,$val) ) {
				$this->setAttr($key,$defaults[$key]);
			}
		}
	}

	/**
	 * Set form token
	 *
	 * @access public
	 * @param string $token
	 * @return void
	 */
	public function setToken($token = '')
	{
		$this->token = $token;
	}

	/**
	 * Validate and set form
	 *
	 * @access public
	 * @param string $key
	 * @param string $val
	 * @return bool
	 */
	public function setAttr($key, $val)
	{
		switch ($key) {

			case 'action':
			case 'id':
			case 'class':
			case 'submit-text':
			case 'submit-class':
				break;

			case 'method':
				$method = ['post','get'];
				if ( !Stringify::contains($method,$val) ) {
					return false;
				}
				break;

			case 'enctype':
				$enctype = ['application/x-www-form-urlencoded','multipart/form-data'];
				if ( !Stringify::contains($enctype,$val) ) {
					return false;
				}
				break;

			case 'novalidate':
			case 'antispam':
			case 'form-element':
			case 'submit':
				if ( !TypeCheck::isBool($val) ) {
					return false;
				}
				break;

			case 'token':
				if ( !TypeCheck::isString($val) && !TypeCheck::isBool($val) ) {
					return false;
				}
				break;

			default:
				return false;
		}

		$this->form[$key] = $val;
		return true;
	}

	/**
	 * Add input field to form
	 *
	 * @access public
	 * @param string $label
	 * @param string $args
	 * @param string $slug
	 * @return void
	 */
	public function addInput($label, $args = [], $slug = '')
	{
		if ( empty($slug) ) {
			$slug = Stringify::slugify($label);
		}

		$defaults = [
			'type'             => 'text',
			'name'             => $slug,
			'id'               => $slug,
			'label'            => $label,
			'class'            => ['form-control'],
			'value'            => '',
			'placeholder'      => '',
			'min'              => '',
			'max'              => '',
			'step'             => '',
			'autofocus'        => false,
			'checked'          => false,
			'selected'         => false,
			'required'         => false,
			'options'          => [],
			'add-label'        => true,
			'wrap-tag'         => 'div',
			'wrap-class'       => ['form-group'],
			'wrap-id'          => '',
			'wrap-style'       => '',
			'before-html'      => '',
			'after-html'       => '',
			'request-populate' => true
		];

		// Combined defaults and arguments
		$args = Arrayify::merge($defaults,$args);
		$this->inputs[$slug] = $args;
	}

	/**
	 * Add multiple inputs to the input queue
	 *
	 * @access public
	 * @param $arr
	 * @return bool
	 */
	public function addInputs($arr)
	{
		if ( !TypeCheck::isArray($arr) ) {
			return false;
		}
		foreach ( $arr as $field ) {
			$args = isset($field[1]) ? $field[1] : '';
			$slug = isset($field[2]) ? $field[2] : '';
			$this->addInput($field[0],$args,$slug);
		}
		return true;
	}

	/**
	 * Generate the HTML for the form based on the input queue
	 *
	 * @access public
	 * @param bool $render
	 * @return string
	 */
	public function generate($render = false)
	{
		$output = '';

		// Add system token field
		if ( $this->form['token'] ) {
			$this->addSystemInput('--token', [
				'type'  => 'hidden',
				'value' => $this->token
			]);
		}

		// Add honeypot anti-spam field
		if ( $this->form['antispam'] ) {
			$this->addSystemInput('--ignore', [
				'wrap-tag'   => 'div',
				'wrap-class' => ['hidden'],
				'wrap-style' => 'display:none'
			]);
		}

		// Add form
		if ( $this->form['form-element'] ) {

			$output .= '<form method="' . $this->form['method'] . '"';

			if ( !empty($this->form['enctype']) ) {
				$output .= ' enctype="' . $this->form['enctype'] . '"';
			}

			if ( !empty($this->form['action']) ) {
				$output .= ' action="' . $this->form['action'] . '"';
			}

			if ( !empty($this->form['id']) ) {
				$output .= ' id="' . $this->form['id'] . '"';
			}

			$output .= $this->outputClasses($this->form['class']);

			if ( $this->form['novalidate'] ) {
				$output .= ' novalidate';
			}

			$output .= '>';
		}

		// Add system input HTML
		foreach ( $this->systemInputs as $val ) {
			$output.= $this->build($val);
		}

		// Add input HTML
		foreach ( $this->inputs as $val ) {
			$output.= $this->build($val);
		}

		// Auto-add submit button
		if ( !$this->hasSubmit && $this->form['submit'] ) {
			$output .= '<div class="form-group">';
			$output .= '<input type="submit" name="submit" ';
			$output .= 'class="'.$this->form['submit-class'].'" ';
			$output .= 'value="'.$this->form['submit-text'].'">';
			$output .= '</div>';
		}

		// Close the form tag if one was added
		if ( $this->form['form-element'] ) {
			$output .= '</form>';
		}

		// Output
		if ( $render ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Add system input field to form
	 *
	 * @access private
	 * @param array $val
	 * @return string
	 */
	private function build($val = [])
	{
		$output = '';

		// Init
		$range = $element = $end = $attr = $field = $label = '';

		// Automatic population of values using Request
		if ( $val['request-populate'] && Request::isSetted($val['name']) ) {
			$types = ['html','title','radio','checkbox','select','submit'];
			if ( !Stringify::contains($types,$val['type']) ) {
				$val['value'] = Request::get($val['name']);
			}
		}

		// Automatic population for checkboxes and radios
		if ( $val['request-populate'] ) {
			if ( $val['type'] == 'radio' || $val['type'] == 'checkbox' ) {
				if ( empty($val['options']) ) {
					$val['checked'] = Request::isSetted($val['name']) ? true : $val['checked'];
				}
			}
		}

		switch ( $val['type'] ) {

			case 'html':
				$element = '';
				$end = $val['label'];
				break;

			case 'title':
				$element = '';
				$end = "<h3>{$val['label']}</h3>";
				break;

			case 'textarea':
				$element = 'textarea';
				$end = ">{$val['value']}</textarea>";
				break;

			case 'select':
				$element = 'select';
				$end .= '>';
				foreach ( $val['options'] as $key => $opt ) {
					$option = '';
					if ( $val['request-populate'] && Request::isSetted($val['name']) ) {
						if ( Request::get($val['name']) === $key ) {
							$option = ' selected';
						}
					} elseif ( $val['selected'] === $key ) {
						$option = ' selected';
					}
					$end .= '<option value="' . $key . '"' . $option . '>' . $opt . '</option>';
				}
				$end .= '</select>';
				break;

			case 'radio':
			case 'checkbox':
				if ( count( $val['options'] ) > 0 ) {
					$element = '';
					foreach ( $val['options'] as $key => $opt ) {
						$slug = Stringify::slugify($opt);
						$pattern = '<input type="%s" name="%s[]" value="%s" id="%s"';
						$end .= sprintf($pattern,$val['type'],$val['name'],$key,$slug);
						if ( $val['request-populate'] && Request::isSetted($val['name']) ) {
							if ( Stringify::contains(Request::get($val['name']),$key) ) {
								$end .= ' checked';
							}
						}
						$end .= '>';
						$end .= ' <label for="' . $slug . '">' . $opt . '</label>';
					}
					$label = '<div class="checkbox-header">' . $val['label'] . '</div>';
					break;
				}

			// All text fields (text, email, url, etc), single radios, single checkboxes, and submit
			default :
				$element = 'input';
				$end .= ' type="' . $val['type'] . '" value="' . $val['value'] . '"';
				if ( isset($val['checked']) ) {
					$end .= $val['checked'] ? ' checked' : '';
				}
				$end .= '>';
				break;
		}

		// Submit button
		if ( $val['type'] === 'submit' ) {
			$this->hasSubmit = true;
		}

		// Special number values for range and number types
		if ( $val['type'] === 'range' || $val['type'] === 'number' ) {
			$range .= ! empty($val['min']) ? ' min="' . $val['min'] . '"' : '';
			$range .= ! empty($val['max']) ? ' max="' . $val['max'] . '"' : '';
			$range .= ! empty($val['step']) ? ' step="' . $val['step'] . '"' : '';
		}

		// Add ID field if present
		$id = !empty($val['id']) ? ' id="' . $val['id'] . '"' : '';

		// Output classes
		$class = $this->outputClasses($val['class']);

		// Special fields
		if ( isset($val['autofocus']) ) {
			$attr .= $val['autofocus'] ? ' autofocus' : '';
		}
		if ( isset($val['checked']) ) {
			$attr .= $val['checked'] ? ' checked' : '';
		}
		if ( isset($val['required']) ) {
			$attr .= $val['required'] ? ' required' : '';
		}

		// Build label
		if ( !empty($label) ) {
			$field .= $label;

		} elseif ( $val['add-label'] && !Stringify::contains(['hidden','submit','title','html'],$val['type']) ) {
			if ( $val['required'] ) {
				$val['label'] .= ' <strong>*</strong>';
			}
			$field .= '<label for="' . $val['id'] . '">' . "{$val['label']}</label>";
		}

		// An $element was set in the $val['type'] switch statement above so use that
		if ( !empty($element) ) {
			if ( $val['type'] === 'checkbox' ) {
				$field = "<{$element}{$id}" . ' name="' . $val['name'] . '"' . "{$range}{$class}{$attr}{$end}{$field}";
			} else {
				$field .= "<{$element}{$id}" . ' name="' . $val['name'] . '"' . "{$range}{$class}{$attr}{$end}";
			}
		} else {
			$field .= $end;
		}

		// Parse and create wrap
		if ( $val['type'] !== 'hidden' && $val['type'] !== 'html' ) {
			$before = $val['before-html'];
			if ( ! empty( $val['wrap-tag'] ) ) {
				$before .= '<' . $val['wrap-tag'];
				$before .= count( $val['wrap-class']) > 0 ? $this->outputClasses( $val['wrap-class'] ) : '';
				$before .= !empty($val['wrap-style']) ? ' style="' . $val['wrap-style'] . '"' : '';
				$before .= !empty($val['wrap-id']) ? ' id="' . $val['wrap-id'] . '"' : '';
				$before .= '>';
			}
			$after = $val['after-html'];
			if ( !empty($val['wrap-tag']) ) {
				$after = "</{$val['wrap-tag']}>{$after}";
			}
			$output .= "{$before}{$field}{$after}";
		} else {
			$output .= $field;
		}

		return $output;
	}

	/**
	 * Add system input field to form
	 *
	 * @access private
	 * @param string $slug
	 * @param string $args
	 * @return void
	 */
	private function addSystemInput($slug, $args = [])
	{
		$defaults = [
			'type'             => 'text',
			'name'             => $slug,
			'id'               => '',
			'class'            => [],
			'value'            => '',
			'min'              => '',
			'max'              => '',
			'step'             => '',
			'required'         => false,
			'add-label'        => false,
			'wrap-tag'         => '',
			'wrap-class'       => [],
			'wrap-id'          => '',
			'wrap-style'       => '',
			'before-html'      => '',
			'after-html'       => '',
			'request-populate' => false
		];

		// Combined defaults and arguments
		$args = Arrayify::merge($defaults,$args);
		$this->systemInputs[$slug] = $args;
	}

	/**
	 * Parses and builds the classes in multiple places
	 *
	 * @access private
	 * @param array $classes
	 * @return string
	 */
	private function outputClasses($classes)
	{
		$output = '';
		if ( TypeCheck::isArray($classes) && count($classes) > 0 ) {
			$output .= ' class="';
			foreach ( $classes as $class ) {
				$output .= $class . ' ';
			}
			$output .= '"';
		} elseif ( TypeCheck::isString($classes) ) {
			$output .= ' class="' . $classes . '"';
		}
		return $output;
	}
}