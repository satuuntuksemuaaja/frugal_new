<?php namespace Vocalogic\Html;

use Collective\Html\HtmlBuilder as BaseHtmlBuilder;

trait GenericBuilder {

	protected $attributes = [];

	protected $html = null;

	/**
	 * Create a new builder instance.
	 *
	 * @param  array  $attributes
	 * @param  \Collective\Html\HtmlBuilder  $html
	 * @return void
	 */
	public function __construct(array $attributes = [], BaseHtmlBuilder $html = null)
	{
		$this->attributes = $attributes;
		$this->html = $html;
	}

	/**
	 * Set the id attribute.
	 *
	 * @param  string  $id
	 * @return mixed
	 */
	public function id($id)
	{
		$this->attributes['id'] = strval($id);
		return $this;
	}

	/**
	 * Set the class attribute.
	 *
	 * @param  string  $classes
	 * @return mixed
	 */
	public function classes($classes)
	{
		$this->attributes['class'] = strval($classes);
		return $this;
	}

	/**
	 * Set the style attribute.
	 *
	 * @param  string  $style
	 * @return mixed
	 */
	public function style($style)
	{
		$this->attributes['style'] = strval($style);
		return $this;
	}

	/**
	 * Render the full HTML contents.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

}
