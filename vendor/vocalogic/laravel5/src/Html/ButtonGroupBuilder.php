<?php namespace Vocalogic\Html;

class ButtonGroupBuilder {

	use GenericBuilder;
	use BootstrapSize;

	protected $buttons = [];

	protected $vertical = false;

	protected $justified = false;

	protected $spaced = false;

	/**
	 * Set the button group buttons.
	 *
	 * @param  array    $buttons
	 * @return Vocalogic\Html\ButtonGroupBuilder
	 */
	public function buttons($buttons)
	{
		$this->buttons = $buttons;
		return $this;
	}

	/**
	 * Make buttons appear vertically stacked.
	 *
	 * @return Vocalogic\Html\ButtonGroupBuilder
	 */
	public function vertical()
	{
		$this->vertical = true;
		return $this;
	}

	/**
	 * Make a group of buttons stretch at equal sizes to span the entire width of its parent.
	 *
	 * @return Vocalogic\Html\ButtonGroupBuilder
	 */
	public function justified()
	{
		$this->justified = true;
		return $this;
	}

	/**
	 * Do not wrap into a btn-group (only render the buttons).
	 *
	 * @return Vocalogic\Html\ButtonGroupBuilder
	 */
	public function spaced()
	{
		$this->spaced = true;
		return $this;
	}

	/**
	 * Obtain Bootstrap button group classes.
	 *
	 * @return string
	 */
	public function getButtonGroupClasses()
	{
		$classes = explode(' ', empty($this->attributes['class']) ? '' : $this->attributes['class']);

		$classes[] = 'btn-group' . ($this->vertical ? '-vertical' : '');

		if ($this->size != 'default')
		{
			$classes[] = 'btn-group-' . $this->size;
		}

		if ($this->justified)
		{
			$classes[] = 'btn-group-justified';
		}

		return join(' ', $classes);
	}

	/**
	 * Render the button group HTML.
	 *
	 * @return string
	 */
	public function render()
	{
		$attributes = $this->attributes;

		$attributes['class'] = $this->getButtonGroupClasses();

		$attributes = $this->html->attributes($attributes);

		if ($this->spaced)
		{
			$html =  join(PHP_EOL, $this->renderedButtons());
		}
		else
		{
			$html = "<div{$attributes}>" . join(PHP_EOL, $this->renderedButtons()) . '</div>';
		}

		return $html;
	}

	public function renderedButtons()
	{
		if (!$this->justified)
		{
			return array_map('strval', $this->buttons);
		}

		$buttons = [];

		foreach ($this->buttons as $button)
		{
			$button = strval($button);

			if (strpos($button, '<div class="btn-group">') === 0)
			{
				$buttons[] = $button;
				continue;
			}

			$buttons[] = '<div class="btn-group">' . $button . '</div>';
		}

		return $buttons;
	}

}
