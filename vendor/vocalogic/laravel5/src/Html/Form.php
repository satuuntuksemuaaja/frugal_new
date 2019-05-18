<?php namespace Vocalogic\Html;

use Vocalogic\Html\FormBuilder;

class Form
{
	public $ajax = false;
	public $binding = null;
	public $method = 'POST';
	public $route = '';
	public $fields = [];
	public $options = [];

	/**
	 * Set the form ajax attribute.
	 *
	 * @param  string  $ajax
	 * @return Form
	 */
	public function ajax($ajax = true)
	{
		$this->ajax = $ajax;
		return $this;
	}

	/**
	 * Set the form binding.
	 *
	 * @param  string  $binding
	 * @return Form
	 */
	public function bind($binding)
	{
		$this->binding = $binding;
		return $this;
	}

	/**
	 * Set the form method.
	 *
	 * @param  string  $method
	 * @return Form
	 */
	public function method($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * Set the form route.
	 *
	 * @param  string  $route
	 * @return Form
	 */
	public function route($route)
	{
		$this->route = $route;
		return $this;
	}

	/**
	 * Set the form fields.
	 *
	 * @param  array  $fields
	 * @return Form
	 */
	public function fields($fields)
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Set options to be applied to the form.
	 *
	 * @param  array  $options
	 * @return Form
	 */
	public function options($options)
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * Get FormBuilder instance with applied options and fields.
	 * Available options:
	 *
	 * ajax - set to true to include ajax attribute
	 * binding - set to a string (vue binding) or model (eloquent binding)
	 * method - form http method (defaults to POST)
	 * route - form action attribute
	 * fields - form fields
	 * init - additional FormBuilder init options
	 * callback - optional function to apply further changes to the form (receives it as parameter)
	 *
	 * @param  array $options
	 * @return FormBuilder
	 */
	public function get($options = [])
	{
		extract(array_merge($this->options, $options));

		$init = isset($init) ? (array) $init : [];

		if (isset($ajax) ? $ajax : $this->ajax)
		{
			$init['ajax'] = true;
		}

		$form = app('form')->vlform($init)
		->bind(isset($binding) ? $binding : $this->binding)
		->method(isset($method) ? $method : $this->method)
		->uri(isset($route) ? $route : $this->route)
		->fields(isset($fields) ? $fields : $this->fields);

		if (isset($callback) && is_callable($callback))
		{
			call_user_func($callback, $this);
		}

		return $form;
	}

	/**
	 * Render the form.
	 *
	 * @param  array  $options   see "get" method description
	 * @return string
	 */
	public function render($options = [])
	{
		return $this->get($options)->render();
	}
}
