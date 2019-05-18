<?php namespace Vocalogic\Html;

use Collective\Html\FormBuilder as BaseFormBuilder;

class FormBuilder extends BaseFormBuilder
{

	protected $attributes = [];

	protected $tag = 'form';

	protected $action = '';

	protected $route = '';

	protected $uri = '';

	protected $id = '';

	protected $method = '';

	protected $class = '';

	protected $binding = '';

	protected $hasFile = false;

	protected $fieldsets = [];

	protected $steps = [];

	protected $decorator = null;

	protected $data = [];

	/**
	 * Build initial form object with optional attributes.
	 *
	 * @param  array  $attributes
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function init(array $attributes = [])
	{
		$form = new FormBuilder($this->html, $this->url, $this->view, $this->csrfToken);
		$form->setSessionStore($this->session);
		$form->attributes = $attributes;
		return $form;
	}

	/**
	 * Build initial vlform object with optional attributes.
	 *
	 * @param  array  $attributes
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function vlform(array $attributes = [])
	{
		$attributes['inline-template'] = true;
		return $this->init($attributes)->tag('vlform');
	}

	/**
	 * Set the form tag.
	 *
	 * @param  mixed  $tag
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function tag($tag)
	{
		$this->tag = $tag;
		return $this;
	}

	/**
	 * Set the form action.
	 *
	 * @param  mixed  $action
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function action($action)
	{
		$this->action = is_array($action) ? $action : func_get_args();
		return $this;
	}

	/**
	 * Set the form route.
	 *
	 * @param  string  $route
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function route($route)
	{
		$this->route = $route;
		return $this;
	}

	/**
	 * Set the form url.
	 *
	 * @param  string  $uri
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function uri($uri)
	{
		$this->uri = $uri;
		return $this;
	}

	/**
	 * Set the form id.
	 *
	 * @param  string  $id
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function id($id)
	{
		$this->id = strval($id);
		return $this;
	}

	/**
	 * Set the form method.
	 *
	 * @param  string  $method
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function method($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * Set the form attributes.
	 *
	 * @param  array  $attributes
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function attributes(array $attributes)
	{
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * Append any additional classes.
	 * The method accepts an array, a string, or several strings.
	 * All these will have the same effect:
	 * $form->classes(array('container', 'col-md-3'))
	 * $form->classes('container col-md-3')
	 * $form->classes('container', 'col-md-3')
	 *
	 * @param  mixed  $classes
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function classes($classes)
	{
		$args = func_get_args();

		if (is_array($classes))
		{
			$this->classes = join(' ', $classes);
		}
		elseif (count($args) > 1)
		{
			$this->classes = join(' ', $args);
		}
		else
		{
			$this->classes = $classes;
		}

		return $this;
	}

	/**
	 * Bind a model to the form.
	 * It will usually be an Eloquent Model, but it can be any object.
	 *
	 * @param  mixed  $model
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function bind($model)
	{
		if (is_string($model))
		{
			$this->binding = $model;
		}
		else
		{
			$this->setModel($model);
		}
		return $this;
	}

	/**
	 * Render the opening HTML form tag with the specified attributes. It is possible
	 * to set id, method and classes, as well as action, route or url.
	 * Set "files" to "true" for files support.
	 *
	 * @param  array  $options
	 * @return string
	 */
	public function open(array $options = [])
	{
		$open = config('vocalogic.formAttributes', []);

		if (!empty($this->id))
		{
			$open['id'] = $this->id;
		}

		if (!empty($this->method))
		{
			$open['method'] = $this->method;
		}

		if (!empty($this->hasFile))
		{
			$open['files'] = true;
		}

		if (!empty($this->action))
		{
			$open['action'] = $this->action;
		}

		if (!empty($this->route))
		{
			$open['route'] = $this->route;
		}

		if (!empty($this->uri))
		{
			$open['url'] = $this->uri;
		}

		$classes = [];
		if (!empty($open['class']))
		{
			$classes[] = $open['class'];
		}
		if (!empty($this->class))
		{
			$classes[] = $this->class;
		}
		if (!empty($options['class']))
		{
			$classes[] = $options['class'];
		}
		if (!empty($classes))
		{
			$options['class'] = join(' ', $classes);
		}

		return parent::open(array_merge($open, $options));
	}

	/**
	 * Take each field and render them through the decorator.
	 *
	 * @param  array $fields
	 * @param  array $attributes
	 * @return string
	 */
	public function renderFields(array $fields, array $attributes = [])
	{
		$html = [];
		foreach ($fields as $name => $field)
		{
			if (!is_numeric($name))
			{
				$field['name'] = $name;
			}
			$html[] = $this->renderField($field, $attributes);
		}
		return join(PHP_EOL, $html);
	}

	/**
	 * Build the fields inside a fieldset. Call multiple times to break into
	 * multiple fieldsets.
	 *
	 * @param  array $fields
	 * @param  array $attributes
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function fields(array $fields, array $attributes = [])
	{
		array_walk($fields, function (&$field) use ($attributes)
		{
			$field = array_merge($attributes, $field);
		});
		$this->fieldsets[] = $fields;
		return $this;
	}

	/**
	 * Render a fieldset including the specified fields and applying the specified attributes.
	 * 
	 * @param  array $fields
	 * @param  array $attributes
	 * @param  array $fieldAttributes
	 * @return string
	 */
	public function fieldset(array $fields, array $attributes = [], array $fieldAttributes = [])
	{
		$legend = '';
		if (isset($attributes['legend']))
		{
			$legend = '<legend>' . $attributes['legend'] . '</legend>';
			unset($attributes['legend']);
		}
		return
			'<fieldset' . $this->html->attributes($attributes) . '>' .
			$legend .
			$this->renderFields($fields, $fieldAttributes) .
			'</fieldset>';
	}

	/**
	 * Renders a set of fieldsets. Each element at $fieldsets parameter is an array of $fields.
	 * 
	 * @param  array  $fieldsets
	 * @param  array  $attributes
	 * @param  array  $fieldAttributes
	 * @return string
	 */
	public function fieldsets(array $fieldsets, array $attributes = [], array $fieldAttributes = [])
	{
		$html = [];
		foreach ($fieldsets as $key => $fields)
		{
			$fieldsetAttributes = $attributes;

			if (isset($this->steps[$key]))
			{
				$fieldsetAttributes = array_merge($this->steps[$key], $fieldsetAttributes);
			}
			$html[] = $this->fieldset($fields, $fieldsetAttributes, $fieldAttributes);
		}
		return join(PHP_EOL, $html);
	}

	/**
	 * Declare a wizard with a steps array. Each element at $steps parameter will be converted into a fieldset,
	 * and must be an array containing three keys: "title" (fieldset attribute), "legend" (legend element text)
	 * and "fields" (an array of field definitions).
	 * 
	 * @param  array  $steps
	 * @return Vocalogic\Html\FormBuilder
	 */
	public function wizard(array $steps)
	{
		foreach ($steps as $step)
		{
			$this->steps[] = ['title' => $step['title'], 'legend' => $step['legend']];
			$this->fields($step['fields']);
		}
		return $this;
	}

	/**
	 * Render the full HTML form.
	 * 
	 * @param  array  $attributes
	 * @param  array  $fieldsetAttributes
	 * @param  array  $fieldAttributes
	 * @return string
	 */
	public function render(array $attributes = [], array $fieldsetAttributes = [], array $fieldAttributes = [])
	{
		$fieldsets = $this->fieldsets($this->fieldsets, $fieldsetAttributes, $fieldAttributes);

		if ($this->tag == 'vlform')
		{
			if ($this->binding)
			{
				$attributes['$data'] = '{{' . $this->binding . '}}';
			}
			else
			{
				$attributes['$data'] = '{' . join(', ', $this->data) . '}';
			}
		}

		$html = [];
		$html[] = str_replace('<form', '<' . $this->tag, $this->open(array_merge($this->attributes, $attributes)));
		$html[] = $fieldsets;
		$html[] = str_replace('</form', '</' . $this->tag, $this->close());

		return join(PHP_EOL, $html);
	}

	/**
	 * Render a single field through the decorator
	 * 
	 * @param  array  $field
	 * @param  array  $attributes
	 * @return string
	 */
	public function renderField($field, array $attributes = [])
	{
		$originalField = $field;
		$this->prepareField($field, $attributes, $decoration);
		$type = $this->getFieldType($field);
		$label = $this->getLabel($field, $type);
		$input = $this->getInput($field, $type);
		return $this->decorate($originalField, $type, $label, $input, $decoration);
	}

	/**
	 * Prepare field attributes and extract decoration attributes.
	 * Apply attribute macros, if available.
	 *
	 * @param  array  $field
	 * @param  array  $attributes
	 * @param  array  $decoration
	 * @return void
	 */
	protected function prepareField(&$field, array $attributes = [], &$decoration = [])
	{
		$fieldClass = empty($field['class']) ? '' : $field['class'];
		$configAttributes = config('vocalogic.fieldAttributes', []);
		$field = array_merge(array_merge($configAttributes, $attributes), $field);

		if (empty($field['name']))
		{
			$field['name'] = '';
		}

		// We don't want to override classes; we want to concatenate them:
		$classes = [];
		if (!empty($configAttributes['class']))
		{
			$classes[] = $configAttributes['class'];
		}
		if (!empty($attributes['class']))
		{
			$classes[] = $attributes['class'];
		}
		if (!empty($fieldClass))
		{
			if (strpos($fieldClass, '!important') === false)
			{
				$classes[] = $fieldClass;
			}
			else
			{
				// if there is "!important" in the class field attribute, we will discard inherited classes
				$classes = [$fieldClass];
			}
		}
		if (!empty($classes))
		{
			$field['class'] = join(' ', $classes);
		}

		$decoration = [];

		// Making it possible to modify attributes into something else by using macros
		foreach ($field as $attribute => $value)
		{
			$attributeMacro = $attribute . 'Attribute';

			if (self::hasMacro($attributeMacro))
			{
				self::$attributeMacro([&$field]);
			}

			if (substr($attribute, 0, 1) == '_')
			{
				$decoration[substr($attribute, 1)] = $value;
				unset($field[$attribute]);
			}
		}

		if ($this->tag == 'vlform')
		{
			if (!empty($field['name']) && empty($field['v-model']))
			{
				$field['v-model'] = $field['name'];
			}
			if (!empty($field['v-model']))
			{
				$this->data[] = $field['v-model'] . ': null';
			}
		}
		elseif (!empty($this->binding) && !empty($field['name']) && empty($field['v-model']))
		{
			$field['v-model'] = $this->binding . '.' . $field['name'];
		}
	}

	/**
	 * Extract and return the field type from the field definition array.
	 * 
	 * @param  array  $field
	 * @param  bool   $unset
	 * @return string
	 */
	protected function getFieldType(&$field, $unset = true)
	{
		if (!empty($field['type']))
		{
			$type = $field['type'];
		}
		elseif (!empty($field['html']))
		{
			$type = 'html';
		}
		elseif (isset($field['raw']))
		{
			$type = 'raw';
		}
		else
		{
			$type = 'text';
		}

		if ($unset)
		{
			unset($field['type']);
		}

		return $type;
	}

	/**
	 * Generate the HTML label for the field, if applicable.
	 * 
	 * @param  array   $field
	 * @param  string  $type
	 * @return string
	 */
	protected function getLabel(&$field, $type = null)
	{
		if (!isset($field['label']))
		{
			return '';
		}
		if (in_array($type, ['button', 'submit', 'reset', 'checkbox', 'radio']))
		{
			return '';
		}

		$label = $field['label'];
		$labelAttributes = isset($field['labelAttributes']) ? $field['labelAttributes'] : [];
		$labelAttributes = array_merge(config('vocalogic.labelAttributes', []), $labelAttributes);
		unset($field['label']);
		unset($field['labelAttributes']);

		return $this->label(empty($field['name']) ? '' : $field['name'], $label, $labelAttributes);
	}

	/**
	 * Generate the HTML input for the field. It usually 
	 * will be <input> but may also be <textarea>,
	 * <select>, <button> or any other tag, by
	 * using "raw", "html" and "label" types,
	 * or a Macro type.
	 * 
	 * @param  array   $field
	 * @param  string  $type
	 * @return string
	 */
	protected function getInput(&$field, $type = null)
	{
		switch ($type)
		{
			case 'raw':
				return $field['raw'];

			case 'html':
				return call_user_func_array([$this->html, array_shift($field['html'])], $field['html']);

			case 'token':
				return $this->token();

			case 'file':
				$this->hasFile = true;

			case 'password':
				$name = isset($field['name']) ? $field['name'] : null;
				unset($field['name']);

				return $this->$type($name, $field);

			case 'image':
				$name = $field['name'];
				unset($field['name']);

				$url = (empty($field['url']) ? $field['src'] : $field['url']);
				unset($field['url']);
				unset($field['src']);

				return $this->image($url, $name, $field);

			case 'button':
			case 'submit':
			case 'reset':
				$value = isset($field['val']) ? $field['val'] : null;
				unset($field['val']);

				$label = isset($field['label']) ? $field['label'] : null;
				unset($field['label']);

				if (empty($value))
				{
					$value = $label;
				}

				return $this->$type($value, $field);

			case 'checkbox':
			case 'radio':
				$inline = !empty($field['inline']);
				unset($field['inline']);

				$name = $field['name'];
				unset($field['name']);

				$value = (array)(isset($field['val']) ? $field['val'] : null);
				unset($field['val']);

				$options = isset($field['opts']) ? $field['opts'] : null;
				unset($field['opts']);

				$textAsValue = (boolean)(isset($field['textAsValue']) ? $field['textAsValue'] : true);
				unset($field['textAsValue']);

				if (is_array($options))
				{
					$input = [];
					foreach ($options as $optValue => $optLabel)
					{
						$option = [
							'type'   => $type,
							'inline' => $inline,
							'name'   => $name,
							'val'    => $value,
							'option' => ($textAsValue ? $optLabel : $optValue),
							'label'  => $optLabel,
						];

						$input[] = $this->renderField($option);
					}
					return join(PHP_EOL, $input);
				}

				$optionValue = isset($field['option']) ? $field['option'] : ($type == 'checkbox' ? '1' : $name);
				unset($field['option']);

				$label = isset($field['label']) ? $field['label'] : null;
				unset($field['label']);

				$checked = in_array($optionValue, $value);
				$input = $this->$type($name, $optionValue, $checked, $field);

				if (empty($label))
				{
					return $input;
				}

				$disabled = empty($field['disabled']) ? '' : 'disabled ';

				if ($inline)
				{
					return '<label class="' . $disabled . $type . '-inline">' . $input . $label . '</label>';
				}
				else
				{
					return '<div class="' . $disabled . $type . '"><label>' . $input . $label . '</label></div>';
				}

			case 'select':
				$name = $field['name'];
				unset($field['name']);

				$value = isset($field['val']) ? $field['val'] : null;
				unset($field['val']);

				$options = isset($field['opts']) ? $field['opts'] : null;
				unset($field['opts']);

				$textAsValue = (boolean)(isset($field['textAsValue']) ? $field['textAsValue'] : true);
				unset($field['textAsValue']);

				if ($textAsValue)
				{
					$options = array_combine($options, $options);
				}

				return $this->select($name, $options, $value, $field);

			case 'selectRange':
			case 'selectYear':
				$name = $field['name'];
				unset($field['name']);

				$value = isset($field['val']) ? $field['val'] : null;
				unset($field['val']);

				$min = isset($field['min']) ? $field['min'] : date('Y');
				$max = isset($field['max']) ? $field['max'] : date('Y') + 5;
				unset($field['min']);
				unset($field['max']);

				return $this->$type($name, $min, $max, $value, $field);

			case 'selectMonth':
				$name = $field['name'];
				unset($field['name']);

				$value = isset($field['val']) ? $field['val'] : null;
				unset($field['val']);

				$format = isset($field['format']) ? $field['format'] : '%B';
				unset($field['format']);

				return $this->selectMonth($name, $value, $field, $format);

			default:
				$name = $field['name'];
				unset($field['name']);

				$value = isset($field['val']) ? $field['val'] : null;
				unset($field['val']);

				if (in_array($type, ['text', 'textarea', 'hidden', 'email', 'url', 'number', 'date', 'label']))
				{
					return $this->$type($name, $value, $field);
				}

				if (self::hasMacro($type))
				{
					return self::$type($name, $this->getValueAttribute($name, $value), [&$field]);
				}

				return $this->input($type, $name, $value, $field);
		}
	}

	/**
	 * Get the final field HTML by applying the decorator (if applicable)
	 * upon the already generated label and input HTML markups
	 * for the field.
	 * 
	 * @param  array   $field       Field definition array
	 * @param  string  $type        Field type
	 * @param  string  $label       Field label HTML
	 * @param  string  $input       Field input HTML
	 * @param  array   $decoration  Decoration attributes array
	 * @return string
	 */
	protected function decorate($field, $type, $label, $input, $decoration)
	{
		if (empty($decoration))
		{
			return $label . $input;
		}

		if (empty($this->decorator))
		{
			$this->decorator = app()->make(__NAMESPACE__ . '\FieldDecoratorInterface');
		}

		return $this->decorator->decorate($field, $type, $label, $input, $decoration);
	}

	/**
	 * Render the full HTML form.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

}
