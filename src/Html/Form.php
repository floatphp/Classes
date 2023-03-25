<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Html Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Html;

use FloatPHP\Classes\Filesystem\{
	TypeCheck, Stringify, Arrayify
};
use FloatPHP\Classes\Http\{
	Request, Server
};

/**
 * Form builder.
 */
class Form
{
	/**
	 * @access private
	 * @var array $attributes
	 * @var array $options
	 * @var string $output
	 * @var string $token
	 * @var array $inputs
	 * @var array $html
	 * @var bool $hasSubmit
	 */
	private $attributes = [];
	private $options = [];
	private $output = '';
	private $token = '';
	private $inputs = [];
	private $html = [];
	private $hasSubmit = false;

	/**
	 * @param array $options
	 * @param array $attributes
	 */
	public function __construct($options = false, $attributes = false)
	{
		// Merge form options
		$this->mergeOptions($options);
		// Merge form attributes
		$this->mergeAttributes($attributes);
	}

	/**
	 * Get form options
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	public function getOptions() : array
	{
		return $this->options;
	}

	/**
	 * Get form attributes
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	public function getAttributes() : array
	{
		return $this->attributes;
	}

	/**
	 * Get form inputs
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	public function getInputs() : array
	{
		return $this->inputs;
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
	 * Set form inputs values
	 *
	 * @access public
	 * @param array $values
	 * @return void
	 */
	public function setValues($values = [])
	{
		if ( !empty($values) ) {
			// Override inputs values
			foreach ($this->inputs as $key => $value) {
				if ( isset($values[$value['name']]) ) {
					if ( $value['type'] == 'select' ) {
						$this->inputs[$key]['selected'] = $values[$value['name']];

					} elseif ( $value['type'] == 'checkbox' || $value['type'] == 'radio' ) {
						if ( count($value['options']) == 1 && $values[$value['name']] == 1 ) {
							$this->inputs[$key]['checked'] = true;
						}

					} else {
						$this->inputs[$key]['value'] = $values[$value['name']];
					}
				}
			}
		}
	}

	/**
	 * Set form inputs
	 *
	 * @access public
	 * @param array $inputs
	 * @return void
	 */
	public function setInputs($inputs = [])
	{
		$this->inputs = $inputs;
	}

	/**
	 * Validate and set form attribute
	 *
	 * @access public
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function setAttribute($key, $value) : bool
	{
		switch ($key) {

			case 'action':
			case 'id':
			case 'class':
			case 'name':
				if ( !TypeCheck::isString($value) ) {
					$value = '';
				}
				break;

			case 'method':
				$var = ['post','get'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var,$value) ) {
					$value = '';
				}
				break;

			case 'autocomplete':
				$var = ['on','off'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var,$value) ) {
					$value = '';
				}
				break;

			case 'enctype':
				$var = ['application/x-www-form-urlencoded','multipart/form-data','text/plain'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var,$value) ) {
					$value = '';
				}
				break;

			case 'target':
				$var = ['_blank','_self','_parent','_top'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var,$value) ) {
					$value = '';
				}
				break;

			case 'rel':
				$var = ['external','help','license','next','nofollow','noopener','noreferrer','opener','prev','search'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var,$value) ) {
					$value = '';
				}
				break;

			case 'novalidate':
				if ( !TypeCheck::isBool($value) ) {
					$value = false;
				}
				break;

			default:
				return false;
		}

		$this->attributes[$key] = $value;
		return true;
	}

	/**
	 * Validate and set form options
	 *
	 * @access public
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function setOptions($key, $value)
	{
		switch ($key) {

			case 'form':
			case 'security':
			case 'submit':
				if ( !TypeCheck::isBool($value) ) {
					$value = true;
				}
				break;

			case 'submit-text':
			case 'submit-name':
			case 'submit-before-html':
			case 'submit-after-html':
				if ( !TypeCheck::isString($value) ) {
					$value = '';
				}
				break;

			case 'submit-class':
			case 'submit-wrap-class':
				if ( !TypeCheck::isString($value) && !TypeCheck::isArray($value) ) {
					$value = '';
				}
				break;

			default:
				return false;
		}

		$this->options[$key] = $value;
		return true;
	}

	/**
	 * Get form source (Security)
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public function getSource() : string
	{
		$source = '';
		if ( TypeCheck::isArray($this->attributes['class']) ) {
			if ( count($this->attributes['class']) > 0 ) {
				$source = Arrayify::shift($this->attributes['class']);
			}
		} elseif ( TypeCheck::isString($this->attributes['class']) ) {
			if ( !empty($this->attributes['class']) ) {
				$source = $this->attributes['class'];
			}
		}
		if ( empty($source) ) {
			if ( !empty($this->attributes['id']) ) {
				$source = $this->attributes['id'];

			} elseif ( !empty($this->attributes['name']) ) {
				$source = $this->attributes['name'];

			} else {
				$source = Server::get('request-uri');
				$source = Stringify::slugify($source);
			}
		}
		return $source;
	}
	
	/**
	 * Add input field
	 *
	 * @access public
	 * @param string $label
	 * @param string $args
	 * @param string $slug
	 * @return void
	 */
	public function addInput($label = '', $args = [], $slug = '')
	{
		if ( empty($slug) ) {
			$slug = Stringify::slugify($label);
		}
		// Combined defaults and arguments
		$args = $this->sanitizeInputAttributes($args);
		$args = Arrayify::merge($this->getDefaultInputAttributes($label,$slug),$args);
		$this->inputs[$slug] = $args;
	}

	/**
	 * Add multiple inputs
	 *
	 * @access public
	 * @param $inputs
	 * @return bool
	 */
	public function addInputs($inputs)
	{
		if ( TypeCheck::isArray($inputs) ) {
			foreach ( $inputs as $input ) {
				$args = isset($input[1]) ? $input[1] : '';
				$slug = isset($input[2]) ? $input[2] : '';
				$this->addInput($input[0],$args,$slug);
			}
			return true;
		}
		return false;
	}

	/**
	 * Generate HTML form
	 *
	 * @access public
	 * @param bool $render
	 * @return string
	 */
	public function generate($render = false)
	{
		// Build form header <form>
		$this->buildHeader();

		// Set security system fields <hidden>
		if ( $this->options['security'] ) {
			$this->buildSystemInput('--token', [
				'type'  => 'hidden',
				'value' => $this->token
			]);
			$this->buildSystemInput('--source', [
				'type'  => 'hidden',
				'value' => $this->getSource(),
			]);
			$this->buildSystemInput('--ignore', [
				'class' => ['d-none','hidden']
			]);
		}

		// Build form fields <input>
		$this->buildBody();

		// Build form submit <submit>
		$this->buildSubmit();

		// Build form closing </form>
		$this->buildClose();

		// Output
		if ( $render ) {
			echo $this->output;
		}
		return $this->output;
	}

	/**
	 * Build form header
	 *
	 * @access private
	 * @param void
	 * @return void
	 */
	private function buildHeader()
	{
		if ( $this->options['form'] ) {
			$this->output .= '<form method="' . $this->attributes['method'] . '"';
			if ( !empty($this->attributes['id']) ) {
				$this->output .= ' id="' . $this->attributes['id'] . '"';
			}
			if ( !empty($this->attributes['enctype']) ) {
				$this->output .= ' enctype="' . $this->attributes['enctype'] . '"';
			}
			if ( !empty($this->attributes['name']) ) {
				$this->output .= ' name="' . $this->attributes['name'] . '"';
			}
			if ( !empty($this->attributes['action']) ) {
				$this->output .= ' action="' . $this->attributes['action'] . '"';
			}
			if ( !empty($this->attributes['class']) ) {
				$classes = $this->outputClasses($this->attributes['class']);
				$this->output .= ' class="' . $classes . '"';
			}
			if ( !empty($this->attributes['autocomplete']) ) {
				$this->output .= ' autocomplete="' . $this->attributes['autocomplete'] . '"';
			}
			if ( !empty($this->attributes['target']) ) {
				$this->output .= ' target="' . $this->attributes['target'] . '"';
			}
			if ( !empty($this->attributes['rel']) ) {
				$this->output .= ' rel="' . $this->attributes['rel'] . '"';
			}
			if ( $this->attributes['novalidate'] ) {
				$this->output .= ' novalidate';
			}
			$this->output .= '>';
		}
	}

	/**
	 * Build form submit
	 *
	 * @access private
	 * @param void
	 * @return void
	 */
	private function buildSubmit()
	{
		if ( !$this->hasSubmit && $this->options['submit'] ) {
			if ( !empty($this->options['submit-before-html']) ) {
				$this->output .= $this->options['submit-before-html'];
			}
			if ( !empty($this->options['submit-wrap-class']) ) {
				$classes = $this->outputClasses($this->options['submit-wrap-class']);
				$this->output .= '<div class="' . $classes . '">';
			}
			// type attribute
			$this->output .= '<input type="submit" ';
			// name attribute
			if ( !empty($this->options['submit-name']) ) {
				$this->output .= 'name="' . $this->options['submit-name'] . '" ';
			}
			// class attribute
			if ( !empty($this->options['submit-class']) ) {
				$this->output .= 'class="' . $this->options['submit-class'] . '" ';
			}
			// value attribute
			$this->output .= 'value="' . $this->options['submit-text'] . '">';
			if ( !empty($this->options['submit-wrap-class']) ) {
				$this->output .= '</div>';
			}
			if ( !empty($this->options['submit-after-html']) ) {
				$this->output .= $this->options['submit-after-html'];
			}
		}
	}

	/**
	 * Build form closing
	 *
	 * @access private
	 * @param void
	 * @return void
	 */
	private function buildClose()
	{
		if ( $this->options['form'] ) {
			$this->output .= '</form>';
		}
	}

	/**
	 * Build form fields
	 * {label}{opening}{element}{content}{attributes}{closing}
	 *
	 * @access private
	 * @param void
	 * @return void
	 */
	private function buildBody()
	{
		foreach ( $this->inputs as $input ) {

			// Init temp html
			$this->html = [
				'before'      => '',
				'label'       => '',
				'opening'     => '',
				'element'     => '',
				'attributes'  => '',
				'content'     => '',
				'closing'     => '',
				'description' => '',
				'after'       => ''
			];

			// Validate input type
			if ( !$this->isValidType($input['type']) ) {
				$input['type'] = 'text';
			}

			// Set global value
			if ( $input['use-request'] ) {
				$except = ['html','title','radio','checkbox','select','submit'];
				if ( !Stringify::contains($except,$input['type']) ) {
					if ( Request::isSetted($input['name']) ) {
						$input['value'] = Request::get($input['name']);
					}
				}
			}

			// Ignore default submit button
			if ( $input['type'] === 'submit' ) {
				$this->hasSubmit = true;
			}

			// Set temp html
			if ( $input['type'] !== 'html' && $input['type'] !== 'title' ) {
				$this->html['before'] = $this->getInputBefore($input);
				if ( $input['display-label'] ) {
					$this->html['label'] = $this->getInputLabel($input);
				}
				$this->html['opening'] = $this->getInputOpening($input['type']);
				$this->html['element'] = $this->getInputElement($input['type']);
				$this->html['attributes'] = $this->getInputAttributes($input);
				$this->html['content'] = $this->getInputContent($input);
				$this->html['closing'] = $this->getInputClosing($input['type']);
				$this->html['description'] = $this->getInputDescription($input);
				$this->html['after'] = $this->getInputAfter($input);
			}

			// Set custom html
			if ( $input['type'] == 'html' ) {
				$this->output .= $input['html'];
			}

			// Set custom title
			if ( $input['type'] == 'title' ) {
				$this->output .= '<';
				$this->output .= $input['title-tag'];
				$this->output .= '>';
				$this->output .= $input['title'];
				$this->output .= '</';
				$this->output .= $input['title-tag'];
				$this->output .= '>';
			}

			// Set output
			$this->output .= $this->html['before'];
			$this->output .= $this->html['label'];
			$this->output .= $this->html['opening'];
			$this->output .= $this->html['element'];
			$this->output .= $this->html['attributes'];
			$this->output .= $this->html['content'];
			$this->output .= $this->html['closing'];
			$this->output .= $this->html['description'];
			$this->output .= $this->html['after'];
		}
	}

	/**
	 * Add system input field to form
	 *
	 * @access private
	 * @param string $slug
	 * @param string $args
	 * @return void
	 */
	private function buildSystemInput($slug, $args = [])
	{
		$default = [
			'type'   => 'text',
			'name'   => $slug,
			'class'  => '',
			'value'  => ''
		];
		$args = Arrayify::merge($default,$args);
		if ( empty($args['value']) && Request::isSetted($args['name']) ) {
			$args['value'] = Request::get($args['name']);
		}
		$classes = $this->outputClasses($args['class']);
		$this->output .= '<input type="' . $args['type'] . '" ';
		$this->output .= 'name="' . $args['name'] . '" ';
		if ( !empty($classes) ) {
			$this->output .= 'class="' . $classes . '" ';
		}
		if ( !empty($args['value']) ) {
			$this->output .= 'value="' . $args['value'] . '"';
		}
		$this->output .= '>';
	}

	/**
	 * Extract classes
	 *
	 * @access private
	 * @param array|string $classes
	 * @return string
	 */
	private function outputClasses($classes = '') : string
	{
		$class = '';
		if ( TypeCheck::isArray($classes) && count($classes) > 0 ) {
			$class = implode(' ', $classes);
		} elseif ( TypeCheck::isString($classes) ) {
			$class .= $classes;
		}
		return $class;
	}

	/**
	 * Get default field attributes
	 *
	 * @access private
	 * @param string $label
	 * @param string $slug
	 * @return array
	 */
	private function getDefaultInputAttributes($label = '', $slug = '')
	{
		return [
			'type'          => 'text',
			'name'          => $slug,
			'label'         => $label,
			'id'            => '',
			'class'         => 'form-control',
			'value'         => '',
			'placeholder'   => '',
			'description'   => '',
			'min'           => '',
			'max'           => '',
			'step'          => '',
			'display-label' => true,
			'multiple'      => false,
			'autofocus'     => false,
			'checked'       => false,
			'disabled'      => false,
			'required'      => false,
			'readonly'      => false,
			'use-request'   => false,
			'selected'      => '',
			'options'       => [],
			'wrap-tag'      => '',
			'wrap-class'    => '',
			'wrap-id'       => '',
			'wrap-style'    => '',
			'before-html'   => '',
			'after-html'    => '',
			'html'          => '',
			'title'         => '',
			'title-tag'     => 'h3'
		];
	}

	/**
	 * Sanitize field attributes
	 *
	 * @access private
	 * @param array $attributes
	 * @return array
	 */
	private function sanitizeInputAttributes($attributes)
	{
		foreach ($attributes as $key => $value) {
			if ( !empty($value) ) {
				switch ($key) {
					case 'wrap-tag':
					case 'title-tag':
						$attributes[$key] = Stringify::replace(['<','>'],'',$value);
						break;
					case 'options':
						if ( TypeCheck::isString($value) ) {
							$attributes[$key] = [$value];
						}
						break;
					case 'label':
					case 'title':
						$attributes[$key] = Stringify::stripTag($value);
						break;
				}
			}
		}
		return $attributes;
	}

	/**
	 * Get form default attribures
	 *
	 * @access private
	 * @param void
	 * @return array
	 */
	private function getDefaultAttribures()
	{
		return [
			'id'           => '',
			'class'        => '',
			'name'         => '',
			'method'       => 'post',
			'enctype'      => 'application/x-www-form-urlencoded',
			'action'       => '',
			'target'       => '',
			'rel'          => '',
			'autocomplete' => '',
			'novalidate'   => false
		];
	}

	/**
	 * Get default form options
	 *
	 * @access private
	 * @param void
	 * @return array
	 */
	private function getDefaultOptions()
	{
		return [
			'form'               => true,
			'security'           => true,
			'submit'             => true,
			'submit-name'        => 'submit',
			'submit-text'        => 'Submit',
			'submit-class'       => 'btn btn-primary',
			'submit-wrap-class'  => '',
			'submit-before-html' => '',
			'submit-after-html'  => ''
		];
	}

	/**
	 * Merge form options
	 *
	 * @access private
	 * @param array $options
	 * @return void
	 */
	private function mergeOptions($options = false)
	{
		if ( $options ) {
			$tmp = Arrayify::merge($this->getDefaultOptions(),$options);
		} else {
			$tmp = $this->getDefaultOptions();
		}
		foreach ( $tmp as $key => $value ) {
			if ( !$this->setOptions($key,$value) ) {
				if ( isset($this->getDefaultOptions()[$key]) ) {
					$this->setOptions($key,$this->getDefaultOptions()[$key]);
				}
			}
		}
	}

	/**
	 * Merge form attributes
	 *
	 * @access private
	 * @param array $attributes
	 * @return void
	 */
	private function mergeAttributes($attributes = false)
	{
		if ( $attributes ) {
			$tmp = Arrayify::merge($this->getDefaultAttribures(),$attributes);
		} else {
			$tmp = $this->getDefaultAttribures();
		}
		foreach ( $tmp as $key => $value ) {
			if ( !$this->setAttribute($key,$value) ) {
				if ( isset($this->getDefaultAttribures()[$key]) ) {
					$this->setAttribute($key,$this->getDefaultAttribures()[$key]);
				}
			}
		}
	}

	/**
	 * Validate type attribute
	 *
	 * @access private
	 * @param string $type
	 * @return bool
	 */
	private function isValidType($type = '') : bool
	{
		$types = [
			'html',
			'title',
			'textarea',
			'select',
			'checkbox',
			'radio',
			'text',
			'submit',
			'file',
			'button',
			'hidden',
			'color',
			'image',
			'time',
			'date',
			'datetime-local',
			'week',
			'month',
			'range',
			'number',
			'tel',
			'reset',
			'search',
			'password',
			'url',
			'email'
		];
		if ( Stringify::contains($types,$type) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get input element
	 *
	 * @access private
	 * @param string $type
	 * @return bool
	 */
	private function getInputElement($type = '') : string
	{
		switch ($type) {
			case 'textarea':
			case 'select':
				return $type;
				break;

			case 'radio':
			case 'checkbox':
				return '';
				break;
			
			default:
				return 'input';
				break;
		}
	}

	/**
	 * Get input closing
	 *
	 * @access private
	 * @param string $type
	 * @return string
	 */
	private function getInputClosing($type = '') : string
	{
		switch ($type) {
			case 'textarea':
			case 'select':
				return '</' . $type . '>';
				break;
			
			default:
				return '';
				break;
		}
	}

	/**
	 * Get input description
	 *
	 * @access private
	 * @param array $input
	 * @return string
	 */
	private function getInputDescription($input = []) : string
	{
		$description = '';
		if ( !empty($input['description']) ) {
			$description = '<small>' . $input['description'] . '</small>';
		}
		return $description;
	}

	/**
	 * Get input label
	 *
	 * @access private
	 * @param array $input
	 * @return string
	 */
	private function getInputLabel($input = []) : string
	{
		$label = '';
		switch ($input['type']) {
			case 'radio':
			case 'checkbox':
				if ( count($input['options']) > 0 ) {
					if ( count($input['options']) > 1 ) {
						$label .= '<p>';
						$label .= $input['label'];
						if ( $input['required'] ) {
							$label .= ' <strong>(*)</strong>';
						}
						$label .= '</p>';
					} else {
						if ( !empty($input['id']) ) {
							$label .= '<label for="' . $input['id'] . '">';
						} else {
							$label .= '<label>';
						}
						$label .= $input['label'];
						if ( $input['required'] ) {
							$label .= ' <strong>(*)</strong>';
						}
						$label .= '</label>';
					}
				}
				break;
			
			default:
				if ( $input['type'] !== 'hidden' && $input['type'] !== 'submit' ) {
					if ( !empty($input['id']) ) {
						$label .= '<label for="' . $input['id'] . '">';
					} else {
						$label .= '<label>';
					}
					$label .= $input['label'];
					if ( $input['required'] ) {
						$label .= ' <strong>(*)</strong>';
					}
					$label .= '</label>';
				}
				break;
		}
		return $label;
	}

	/**
	 * Get input before html
	 *
	 * @access private
	 * @param array $input
	 * @return string
	 */
	private function getInputBefore($input = []) : string
	{
		$before = '';
		if ( $input['type'] !== 'html' && $input['type'] !== 'hidden' ) {
			$before .= $input['before-html'];
			if ( !empty($input['wrap-tag']) ) {
				$before .= '<' . $input['wrap-tag'];
				if ( !empty($input['wrap-id']) ) {
					$before .= ' id="' . $input['wrap-id'] . '"';
				}
				if ( !empty($input['wrap-class']) ) {
					$class = $this->outputClasses($input['wrap-class']);
					$before .= ' class="' . $class . '"';
				}
				if ( !empty($input['wrap-style']) ) {
					$before .= ' style="' . $input['wrap-style'] . '"';
				}
				$before .= '>';
			}
		}
		return $before;
	}

	/**
	 * Get input before html
	 *
	 * @access private
	 * @param array $input
	 * @return string
	 */
	private function getInputAfter($input = []) : string
	{
		$after = '';
		if ( $input['type'] !== 'html' && $input['type'] !== 'hidden' ) {
			if ( !empty($input['wrap-tag']) ) {
				$after .= '</' . $input['wrap-tag'] . '>';
			}
			$after .= $input['after-html'];
		}
		return $after;
	}

	/**
	 * Get input opening
	 *
	 * @access private
	 * @param string $type
	 * @return string
	 */
	private function getInputOpening($type = '') : string
	{
		switch ($type) {
			case 'radio':
			case 'checkbox':
				return '';
				break;
			
			default:
				return '<';
				break;
		}
	}

	/**
	 * Get input content
	 *
	 * @access private
	 * @param array $input
	 * @return string
	 */
	private function getInputContent($input) : string
	{
		$content = '';
		switch ($input['type']) {
			case 'textarea':
				$content = $input['value'];
				break;

			case 'select':
				foreach ( $input['options'] as $key => $option ) {
					$selected = false;
					if ( $input['use-request'] ) {
						if ( Request::isSetted($input['name']) ) {
							if ( Request::get($input['name']) === $key ) {
								$selected = true;
							}
						}
					} elseif ( $input['selected'] === $key ) {
						$selected = true;
					}
					$content .= '<option value="' . $key . '"';
					if ( $selected ) {
						$content .= ' selected';
					}
					if ( $input['multiple'] ) {
						$content .= ' multiple';
					}
					$content .= '>' . $option . '</option>';
				}
				break;

			case 'radio':
			case 'checkbox':
				if ( count($input['options']) > 0 ) {
					foreach ( $input['options'] as $key => $option ) {

						// checked input
						$checked = false;
						if ( $input['checked'] ) {
							$checked = true;
						}
						if ( !$checked ) {
							if ( $input['use-request'] ) {
								if ( Request::isSetted($input['name']) ) {
									if ( Stringify::contains(Request::get($input['name']),$key) ) {
										$checked = true;
									}
								}
							}
						}
						

						// Open input
						$content .= '<input';

						// id attribute
						if ( count($input['options']) > 1 ) {
							$slug = Stringify::slugify($option);
							$content .= ' id="' . $slug . '"';
						} else {
							if ( !empty($input['id']) ) {
								$content .= ' id="' . $input['id'] . '"';
							}
						}

						// type attribute
						$content .= ' type="' . $input['type'] . '"';

						// name attribute
						$content .= ' name="' . $input['name'] . '';
						if ( count($input['options']) > 1 ) {
							$content .= '[]';
						}
						$content .= '"';

						// class attribute
						$class = $this->outputClasses($input['class']);
						if ( !empty($class) ) {
							$content .= ' class="' . $class . '"';
						}

						// value attribute
						if ( count($input['options']) > 1 ) {
							$content .= ' value="' . $key . '"';
						}
						
						// Single attribute
						if ( $checked ) {
							$content .= ' checked';
						}
						if ( $input['required'] ) {
							$content .= ' required';
						}

						// Close input
						$content .= '>';
						if ( count($input['options']) > 1 ) {
							$content .= '<label for="' . $slug . '">' . $option . '</label>';
						}
					}
				}
				break;
		}
		return $content;
	}

	/**
	 * Get input attributes
	 *
	 * @access private
	 * @param array $input
	 * @return string
	 */
	private function getInputAttributes($input) : string
	{
		$attributes = '';
		if ( $input['type'] !== 'radio' && $input['type'] !== 'checkbox' ) {
			$attributes = ' ';
			// id attributes
			if ( !empty($input['id']) ) {
				$attributes .= 'id="' . $input['id'] . '" ';
			}
			// type textarea
			if ( $input['type'] !== 'textarea' && $input['type'] !== 'select' ) {
				if ( !empty($input['type']) ) {
					$attributes .= 'type="' . $input['type'] . '" ';
				}
			}
			// name attributes
			if ( !empty($input['name']) ) {
				$attributes .= 'name="' . $input['name'] . '" ';
			}
			// class attributes
			if ( $input['type'] !== 'hidden' ) {
				if ( !empty($input['class']) ) {
					$class = $this->outputClasses($input['class']);
					$attributes .= 'class="' . $class . '" ';
				}
			}
			// placeholder attributes
			if ( $input['type'] !== 'textarea' && $input['type'] !== 'select' ) {
				if ( !empty($input['placeholder']) ) {
					$attributes .= 'placeholder="' . $input['placeholder'] . '" ';
				}
			}
			// value attributes
			if ( $input['type'] !== 'textarea' && $input['type'] !== 'select' ) {
				if ( !empty($input['value']) ) {
					$attributes .= 'value="' . $input['value'] . '" ';
				}
			}
			// Special attributes
			if ( $input['type'] == 'number' || $input['type'] == 'range' ) {
				if ( !empty($input['min']) ) {
					$attributes .= 'min="' . $input['min'] . '" ';
				}
				if ( !empty($input['max']) ) {
					$attributes .= 'max="' . $input['max'] . '" ';
				}
				if ( !empty($input['step']) ) {
					$attributes .= 'step="' . $input['step'] . '" ';
				}
			}
			// style attribute
			if ( !empty($input['style']) ) {
				$attributes .= 'style="' . $input['style'] . '" ';
			}
			// Single attributes
			if ( $input['autofocus'] ) {
				$attributes .= 'autofocus ';
			}
			if ( $input['disabled'] ) {
				$attributes .= 'disabled ';
			}
			if ( $input['required'] ) {
				$attributes .= 'required ';
			}
			if ( $input['readonly'] ) {
				$attributes .= 'readonly ';
			}
			$attributes = rtrim($attributes);
			$attributes = $attributes . '>';
		}
		return $attributes;
	}
}