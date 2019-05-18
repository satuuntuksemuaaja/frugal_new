<?php namespace Vocalogic\Html;

use Collective\Html\HtmlBuilder as BaseHtmlBuilder;

class HtmlBuilder extends BaseHtmlBuilder {

	/**
	 * Generate a Bootstrap grid row.
	 *
	 * @param  mixed   $cols
	 * @param  array   $attributes
	 * @return string
	 */
	public function row($cols, array $attributes = [])
	{
		if (!isset($attributes['class']))
		{
			$attributes['class'] = 'row';
		}
		else
		{
			$attributes['class'] = 'row ' . $attributes['class'];
		}

		$attributes = $this->attributes($attributes);

		$html = [
			"<div{$attributes}>",
			join(PHP_EOL, (array) $cols),
			'</div>',
		];

		return join(PHP_EOL, $html);
	}

	/**
	 * Generate a Bootstrap grid column.
	 *
	 * @param  mixed   $contents
	 * @param  mixed   $sizes
	 * @param  array   $attributes
	 * @return string
	 */
	public function col($contents, $sizes = 12, array $attributes = [])
	{
		$classes = $this->getColumnClasses($sizes);

		if (!isset($attributes['class']))
		{
			$attributes['class'] = $classes;
		}
		else
		{
			$attributes['class'] = $classes . ' ' . $attributes['class'];
		}

		$attributes = $this->attributes($attributes);

		$html = [
			"<div{$attributes}>",
			(string) $contents,
			'</div>',
		];

		return join(PHP_EOL, $html);
	}

	/**
	 * Obtain Bootstrap grid column classes.
	 *
	 * @param  mixed   $sizes
	 * @return string
	 */
	public function getColumnClasses($sizes = 12)
	{
		$classes = explode(' ', $sizes);

		array_walk($classes, function(&$spec)
		{
			if (is_numeric($spec))
			{
				$spec = "col-md-{$spec}";
			}
			elseif (preg_match('/^[a-z]{2}-(offset-|push-|pull-)?\d{1,2}$/', $spec))
			{
				$spec = "col-{$spec}";
			}
			elseif (preg_match('/^(offset-|push-|pull-)\d{1,2}$/', $spec))
			{
				$spec = "col-md-{$spec}";
			}
		});

		return join(' ', $classes);
	}

	/**
	 * Generate a Bootstrap panel.
	 *
	 * @param  array  $attributes
	 * @return Vocalogic\Html\PanelBuilder
	 */
	public function panel(array $attributes = [])
	{
		return new PanelBuilder($attributes, $this);
	}

	/**
	 * Generate a Bootstrap table.
	 *
	 * @param  array  $attributes
	 * @return Vocalogic\Html\TableBuilder
	 */
	public function table(array $attributes = [])
	{
		return new TableBuilder($attributes, $this);
	}

	/**
	 * Generate a Bootstrap button.
	 *
	 * @param  array  $attributes
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function button(array $attributes = [])
	{
		return new ButtonBuilder($attributes, $this);
	}

	/**
	 * Generate a Bootstrap button group.
	 *
	 * @param  array  $buttons
	 * @param  array  $attributes
	 * @return Vocalogic\Html\ButtonGroupBuilder
	 */
	public function buttons(array $buttons = [], array $attributes = [])
	{
		return with(new ButtonGroupBuilder($attributes, $this))->buttons($buttons);
	}

	/**
	 * Generate a Bootstrap toolbar.
	 *
	 * @param  array  $buttonGroups
	 * @param  array  $groupsAttributes
	 * @param  array  $attributes
	 * @return string
	 */
	public function toolbar(array $buttonGroups = [], array $groupsAttributes = [], array $attributes = [])
	{
		if (!isset($attributes['class']))
		{
			$attributes['class'] = 'btn-toolbar';
		}
		else
		{
			$attributes['class'] .= ' ' . 'btn-toolbar';
		}

		$attributes = $this->attributes($attributes);

		$html = ["<div{$attributes}>"];

		foreach ($buttonGroups as $buttons)
		{
			$html[] = $this->buttons($buttons, $groupsAttributes);
		}

		$html[] = '</div>';

		return join(PHP_EOL, $html);
	}

}
