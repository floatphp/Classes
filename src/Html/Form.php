<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Html Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Html;

use FloatPHP\Classes\Filesystem\{TypeCheck, Stringify, Arrayify};
use FloatPHP\Classes\Http\Request;

/**
 * Built-in form generator class.
 */
class Form
{
	/**
	 * @access protected
	 * @var array $atts Form attributes
	 * @var array $options Form options
	 * @var array $inputs Form inputs
	 * @var array $values Form values
	 * @var array $default Form default values
	 * @var array $vars Form vars
	 * @var array $html Form HTML wrapper
	 * @var string $output Form HTML output
	 * @var bool $hasSubmit Form submit status
	 */
	protected $atts = [];
	protected $options = [];
	protected $inputs = [];
	protected $values = [];
	protected $default = [];
	protected $vars = [];
	protected $html = [];
	protected $output = '';
	protected $hasSubmit = false;

	/**
	 * Init form.
	 * 
	 * @param array $options
	 * @param array $atts
	 */
	public function __construct(array $options = [], array $atts = [])
	{
		$this->mergeOptions($options);
		$this->mergeAtts($atts);
	}

	/**
	 * Get form options.
	 *
	 * @access public
	 * @return array
	 */
	public function getOptions() : array
	{
		return $this->options;
	}

	/**
	 * Get form attributes.
	 *
	 * @access public
	 * @return array
	 */
	public function getAtts() : array
	{
		return $this->atts;
	}

	/**
	 * Get form inputs.
	 *
	 * @access public
	 * @return array
	 */
	public function getInputs() : array
	{
		return $this->inputs;
	}

	/**
	 * Set form inputs.
	 *
	 * @access public
	 * @param array $inputs
	 * @return object
	 */
	public function setInputs(array $inputs = []) : self
	{
		$this->inputs = $inputs;
		return $this;
	}

	/**
	 * Set form inputs values.
	 *
	 * @access public
	 * @param array $values
	 * @return object
	 */
	public function setValues(array $values = []) : self
	{
		$this->values = $values;
		return $this;
	}

	/**
	 * Set form inputs default values.
	 *
	 * @access public
	 * @param array $default
	 * @return object
	 */
	public function setDefault(array $default = []) : self
	{
		$this->default = $default;
		return $this;
	}

	/**
	 * Set form dynamic vars.
	 *
	 * @access public
	 * @param array $vars
	 * @return object
	 */
	public function setVars(array $vars = []) : self
	{
		$this->vars = $vars;
		return $this;
	}

	/**
	 * Set and validate form attribute.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function setAttribute(string $key, $value) : bool
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
				$var = ['post', 'get'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var, $value) ) {
					$value = '';
				}
				break;

			case 'autocomplete':
				$var = ['on', 'off'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var, $value) ) {
					$value = '';
				}
				break;

			case 'enctype':
				$var = ['application/x-www-form-urlencoded', 'multipart/form-data', 'text/plain'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var, $value) ) {
					$value = '';
				}
				break;

			case 'target':
				$var = ['_blank', '_self', '_parent', '_top'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var, $value) ) {
					$value = '';
				}
				break;

			case 'rel':
				$var = ['external', 'help', 'license', 'next', 'nofollow', 'noopener', 'noreferrer', 'opener', 'prev', 'search'];
				if ( !TypeCheck::isString($value) || !Stringify::contains($var, $value) ) {
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

		$this->atts[$key] = $value;
		return true;
	}

	/**
	 * Set and validate form options.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function setOptions(string $key, $value) : bool
	{
		switch ($key) {

			case 'form':
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
	 * Add input field.
	 *
	 * @access public
	 * @param mixed $label
	 * @param array $args
	 * @param string $slug
	 * @return void
	 */
	public function addInput($label, array $args = [], ?string $slug = null)
	{
		if ( !$slug ) {
			$slug = Stringify::slugify($label);
		}
		$args = $this->sanitizeInputAtts($args);
		$args = Arrayify::merge($this->getDefaultInputAttrs($label, $slug), $args);
		$this->inputs[$slug] = $args;
	}

	/**
	 * Add multiple inputs.
	 *
	 * @access public
	 * @param array $inputs
	 * @return void
	 */
	public function addInputs(array $inputs) : void
	{
		foreach ($inputs as $key => $args) {
			$label = $input['label'] ?? "{$key}";
			$slug = $input['slug'] ?? null;
			$this->addInput($label, $args, $slug);
		}
	}

	/**
	 * Generate HTML form.
	 *
	 * @access public
	 * @return string
	 */
	public function generate() : string
	{
		// Apply default
		$this->applyDefault();

		// Apply values
		$this->applyValues();

		// Build form header <form>
		$this->buildHeader();

		// Build form fields <input>
		$this->buildBody();

		// Build form submit <submit>
		$this->buildSubmit();

		// Build form closing </form>
		$this->buildClose();

		// Apply vars
		$this->applyVars();

		// Reset
		$output = $this->output;
		$this->inputs = [];
		$this->output = '';

		return $output;
	}

	/**
	 * Render HTML form.
	 *
	 * @access public
	 * @return void
	 */
	public function render() : void
	{
		echo $this->generate();
	}

	/**
	 * Build form header.
	 *
	 * @access protected
	 * @return void
	 */
	protected function buildHeader() : void
	{
		if ( $this->options['form'] ) {
			$this->output .= '<form method="' . $this->atts['method'] . '"';
			if ( !empty($this->atts['id']) ) {
				$this->output .= ' id="' . $this->atts['id'] . '"';
			}
			if ( !empty($this->atts['enctype']) ) {
				$this->output .= ' enctype="' . $this->atts['enctype'] . '"';
			}
			if ( !empty($this->atts['name']) ) {
				$this->output .= ' name="' . $this->atts['name'] . '"';
			}
			if ( !empty($this->atts['action']) ) {
				$this->output .= ' action="' . $this->atts['action'] . '"';
			}
			if ( !empty($this->atts['class']) ) {
				$classes = $this->outputClasses($this->atts['class']);
				$this->output .= ' class="' . $classes . '"';
			}
			if ( !empty($this->atts['autocomplete']) ) {
				$this->output .= ' autocomplete="' . $this->atts['autocomplete'] . '"';
			}
			if ( !empty($this->atts['target']) ) {
				$this->output .= ' target="' . $this->atts['target'] . '"';
			}
			if ( !empty($this->atts['rel']) ) {
				$this->output .= ' rel="' . $this->atts['rel'] . '"';
			}
			if ( $this->atts['novalidate'] ) {
				$this->output .= ' novalidate';
			}
			$this->output .= '>';
		}
	}

	/**
	 * Build form submit.
	 *
	 * @access protected
	 * @return void
	 */
	protected function buildSubmit() : void
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
	 * Build form closing.
	 *
	 * @access protected
	 * @return void
	 */
	protected function buildClose() : void
	{
		if ( $this->options['form'] ) {
			$this->output .= '</form>';
		}
	}

	/**
	 * Build form fields :
	 * {label}{opening}{element}{content}{attributes}{closing}.
	 *
	 * @access protected
	 * @return void
	 */
	protected function buildBody() : void
	{
		foreach ($this->inputs as $input) {

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
				$except = ['html', 'string', 'title', 'radio', 'checkbox', 'select', 'submit'];
				if ( !Stringify::contains($except, $input['type']) ) {
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
			if (
				$input['type'] !== 'html'
				&& $input['type'] !== 'title'
				&& $input['type'] !== 'string'
			) {
				$this->html['before'] = $this->getInputBefore($input);
				if ( $input['display-label'] ) {
					$this->html['label'] = $this->getInputLabel($input);
				}
				$this->html['opening'] = $this->getInputOpening($input['type']);
				$this->html['element'] = $this->getInputElement($input['type']);
				$this->html['attributes'] = $this->getInputAtts($input);
				$this->html['content'] = $this->getInputContent($input);
				$this->html['closing'] = $this->getInputClosing($input['type']);
				$this->html['description'] = $this->getInputDescription($input);
				$this->html['after'] = $this->getInputAfter($input);
			}

			// Set custom html
			if ( $input['type'] == 'html' ) {
				$this->output .= $input['html'];
			}

			// Set custom string
			if ( $input['type'] == 'string' ) {
				$this->output .= $input['string'];
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
	 * Extract classes.
	 *
	 * @access protected
	 * @param mixed $classes
	 * @return string
	 */
	protected function outputClasses($classes = '') : string
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
	 * Get default field attributes.
	 *
	 * @access protected
	 * @param mixed $label
	 * @param string $slug
	 * @return array
	 */
	protected function getDefaultInputAttrs($label, ?string $slug = null) : array
	{
		return [
			'type'          => 'text',
			'name'          => (string)$slug,
			'label'         => (string)$label,
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
			'string'        => '',
			'title'         => '',
			'title-tag'     => 'h3'
		];
	}

	/**
	 * Sanitize field attributes.
	 *
	 * @access protected
	 * @param array $atts
	 * @return array
	 */
	protected function sanitizeInputAtts(array $atts) : array
	{
		foreach ($atts as $key => $value) {
			if ( !empty($value) ) {
				switch ($key) {
					case 'wrap-tag':
					case 'title-tag':
						$atts[$key] = Stringify::remove(['<', '>'], $value);
						break;
					case 'options':
						if ( TypeCheck::isString($value) ) {
							$atts[$key] = [$value];
						}
						break;
					case 'label':
					case 'title':
						$atts[$key] = Stringify::stripTag($value);
						break;
				}
			}
		}
		return $atts;
	}

	/**
	 * Get form default attribures.
	 *
	 * @access protected
	 * @return array
	 */
	protected function getDefaultAtts() : array
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
	 * Get default form options.
	 *
	 * @access protected
	 * @return array
	 */
	protected function getDefaultOptions() : array
	{
		return [
			'form'               => true,
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
	 * Merge form options.
	 *
	 * @access protected
	 * @param array $options
	 * @return void
	 */
	protected function mergeOptions(array $options = []) : void
	{
		$options = Arrayify::merge($this->getDefaultOptions(), $options);
		foreach ($options as $key => $value) {
			if ( !$this->setOptions($key, $value) ) {
				if ( isset($this->getDefaultOptions()[$key]) ) {
					$this->setOptions($key, $this->getDefaultOptions()[$key]);
				}
			}
		}
	}

	/**
	 * Merge form attributes.
	 *
	 * @access protected
	 * @param array $atts
	 * @return void
	 */
	protected function mergeAtts(array $atts = []) : void
	{
		$atts = Arrayify::merge($this->getDefaultAtts(), $atts);
		foreach ($atts as $key => $value) {
			if ( !$this->setAttribute($key, $value) ) {
				if ( isset($this->getDefaultAtts()[$key]) ) {
					$this->setAttribute($key, $this->getDefaultAtts()[$key]);
				}
			}
		}
	}

	/**
	 * Validate type attribute.
	 *
	 * @access protected
	 * @param string $type
	 * @return bool
	 */
	protected function isValidType(string $type) : bool
	{
		$types = [
			'html',
			'string',
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
		if ( Stringify::contains($types, $type) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get input element
	 *
	 * @access protected
	 * @param string $type
	 * @return bool
	 */
	protected function getInputElement(string $type = '') : string
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
	 * Get input closing.
	 *
	 * @access protected
	 * @param string $type
	 * @return string
	 */
	protected function getInputClosing(string $type = '') : string
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
	 * Get input description.
	 *
	 * @access protected
	 * @param array $input
	 * @return string
	 */
	protected function getInputDescription(array $input = []) : string
	{
		$description = '';
		if ( !empty($input['description']) ) {
			$description = '<small>' . $input['description'] . '</small>';
		}
		return $description;
	}

	/**
	 * Get input label.
	 *
	 * @access protected
	 * @param array $input
	 * @return string
	 */
	protected function getInputLabel(array $input = []) : string
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
	 * Get input before html.
	 *
	 * @access protected
	 * @param array $input
	 * @return string
	 */
	protected function getInputBefore(array $input = []) : string
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
	 * Get input before html.
	 *
	 * @access protected
	 * @param array $input
	 * @return string
	 */
	protected function getInputAfter(array $input = []) : string
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
	 * Get input opening.
	 *
	 * @access protected
	 * @param string $type
	 * @return string
	 */
	protected function getInputOpening(string $type = '') : string
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
	 * Get input content.
	 *
	 * @access protected
	 * @param array $input
	 * @return string
	 */
	protected function getInputContent(array $input = []) : string
	{
		$content = '';
		switch ($input['type']) {
			case 'textarea':
				$content = $input['value'];
				break;

			case 'select':
				foreach ($input['options'] as $key => $option) {
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
					foreach ($input['options'] as $key => $option) {

						// checked input
						$checked = false;
						if ( $input['checked'] ) {
							$checked = true;
						}

						if ( !$checked ) {
							if ( $input['use-request'] ) {
								if ( Request::isSetted($input['name']) ) {
									if ( Stringify::contains(Request::get($input['name']), $key) ) {
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
	 * Get input attributes.
	 *
	 * @access protected
	 * @param array $input
	 * @return string
	 */
	protected function getInputAtts(array $input) : string
	{
		$atts = '';
		if ( $input['type'] !== 'radio' && $input['type'] !== 'checkbox' ) {

			$atts = ' ';

			// id attributes
			if ( !empty($input['id']) ) {
				$atts .= 'id="' . $input['id'] . '" ';
			}

			// type textarea
			if ( $input['type'] !== 'textarea' && $input['type'] !== 'select' ) {
				if ( !empty($input['type']) ) {
					$atts .= 'type="' . $input['type'] . '" ';
				}
			}

			// name attributes
			if ( !empty($input['name']) ) {
				$atts .= 'name="' . $input['name'] . '" ';
			}

			// class attributes
			if ( $input['type'] !== 'hidden' ) {
				if ( !empty($input['class']) ) {
					$class = $this->outputClasses($input['class']);
					$atts .= 'class="' . $class . '" ';
				}
			}

			// placeholder attributes
			if ( $input['type'] !== 'textarea' && $input['type'] !== 'select' ) {
				if ( !empty($input['placeholder']) ) {
					$atts .= 'placeholder="' . $input['placeholder'] . '" ';
				}
			}

			// value attributes
			if ( $input['type'] !== 'textarea' && $input['type'] !== 'select' ) {
				if ( !empty($input['value']) ) {
					$atts .= 'value="' . $input['value'] . '" ';
				}
			}

			// Special attributes
			if ( $input['type'] == 'number' || $input['type'] == 'range' ) {
				if ( !empty($input['min']) ) {
					$atts .= 'min="' . $input['min'] . '" ';
				}
				if ( !empty($input['max']) ) {
					$atts .= 'max="' . $input['max'] . '" ';
				}
				if ( !empty($input['step']) ) {
					$atts .= 'step="' . $input['step'] . '" ';
				}
			}

			// style attribute
			if ( !empty($input['style']) ) {
				$atts .= 'style="' . $input['style'] . '" ';
			}

			// Single attributes
			if ( $input['autofocus'] ) {
				$atts .= 'autofocus ';
			}
			if ( $input['disabled'] ) {
				$atts .= 'disabled ';
			}
			if ( $input['required'] ) {
				$atts .= 'required ';
			}
			if ( $input['readonly'] ) {
				$atts .= 'readonly ';
			}
			$atts = rtrim($atts);
			$atts = $atts . '>';
		}
		return $atts;
	}

	/**
	 * Apply form inputs values.
	 *
	 * @access protected
	 * @return void
	 */
	protected function applyValues() : void
	{
		foreach ($this->inputs as $key => $value) {
			if ( isset($this->values[$value['name']]) ) {

				if ( $value['type'] == 'select' ) {
					$this->inputs[$key]['selected'] = $this->values[$value['name']];
				} elseif ( $value['type'] == 'checkbox' || $value['type'] == 'radio' ) {
					if ( count($value['options']) == 1 && $this->values[$value['name']] == 1 ) {
						$this->inputs[$key]['checked'] = true;
					}
				} else {
					$this->inputs[$key]['value'] = $this->values[$value['name']];
				}
			}
		}
	}

	/**
	 * Apply form inputs (select) default values.
	 *
	 * @access protected
	 * @return void
	 */
	protected function applyDefault() : void
	{
		foreach ($this->inputs as $key => $value) {
			if ( isset($this->default[$value['name']]) ) {
				if ( $value['type'] == 'select' && empty($value['options']) ) {
					if ( TypeCheck::isArray($this->default[$value['name']]) ) {
						$this->inputs[$key]['options'] = $this->default[$value['name']];
					}
				}
			}
		}
	}

	/**
	 * Apply form vars.
	 *
	 * @access protected
	 * @return void
	 */
	protected function applyVars() : void
	{
		$this->output = Stringify::replaceArray($this->vars, $this->output);
	}
}
