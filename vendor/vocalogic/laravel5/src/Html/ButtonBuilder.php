<?php namespace Vocalogic\Html;

class ButtonBuilder {

	use GenericBuilder;
	use BootstrapContext;
	use BootstrapSize;

	// link, button, input or submit
	protected $element = 'button';

	protected $elementTags = [
		'link'   => 'a',
		'button' => 'button',
		'input'  => 'input',
		'submit' => 'input'
	];

	protected $block = false;

	protected $active = false;

	protected $disabled = false;

	protected $text = '';

	protected $body = '';

	protected $icon = '';

	protected $dropdown = [];

	protected $splitDropdown = false;

	protected $dropup = false;

	protected $centered = false;

	/**
	 * Set the href attribute.
	 *
	 * @param  string  $url
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function url($url)
	{
		$this->attributes['href'] = strval($url);
		return $this;
	}

	/**
	 * Set the element to 'link' (anchor)
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function link()
	{
		$this->element = 'link';
		return $this;
	}

	/**
	 * Set the element to 'button'
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function button()
	{
		$this->element = 'button';
		return $this;
	}

	/**
	 * Set the element to 'input'
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function input()
	{
		$this->element = 'input';
		return $this;
	}

	/**
	 * Set the element to 'submit'
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function submit()
	{
		$this->element = 'submit';
		return $this;
	}

	/**
	 * Turn the block level button feature on.
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function block()
	{
		$this->block = true;
		return $this;
	}

	/**
	 * Turn the active state on.
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function active()
	{
		$this->active = true;
		return $this;
	}

	/**
	 * Turn the disabled state on.
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function disabled()
	{
		$this->disabled = true;
		return $this;
	}

	/**
	 * Set the button text.
	 *
	 * @param  mixed    $text
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function text($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * Set the button HTML body.
	 *
	 * @param  mixed    $body
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function body($body)
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * Set the button icon.
	 *
	 * @param  mixed    $icon
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function icon($icon)
	{
		$this->icon = $icon;
		return $this;
	}

	/**
	 * Set the button dropdown menu items.
	 *
	 * @param  array    $items
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function dropdown($items, $splitDropdown = false)
	{
		$this->dropdown = $items;
		$this->splitDropdown = $splitDropdown;
		return $this;
	}

	/**
	 * Set the button dropup menu items.
	 *
	 * @param  array    $items
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function dropup($items, $splitDropup = false)
	{
		$this->dropdown($items, $splitDropup);
		$this->dropup = true;
		return $this;
	}

	/**
	 * Wrap button with '<center>' tag.
	 *
	 * @return Vocalogic\Html\ButtonBuilder
	 */
	public function centered()
	{
		$this->centered = true;
		return $this;
	}

	/**
	 * Obtain Bootstrap button classes.
	 *
	 * @return string
	 */
	public function getButtonClasses()
	{
		$classes = explode(' ', empty($this->attributes['class']) ? '' : $this->attributes['class']);

		$classes[] = 'btn';
		$classes[] = 'btn-' . $this->context;

		if ($this->size != 'default')
		{
			$classes[] = 'btn-' . $this->size;
		}

		if ($this->block)
		{
			$classes[] = 'btn-block';
		}

		if ($this->active)
		{
			$classes[] = 'active';
		}

		if ($this->disabled && ($this->element == 'link'))
		{
			$classes[] = 'disabled';
		}

		if ($this->dropdown && !$this->splitDropdown)
		{
			$classes[] = 'dropdown-toggle';
		}

		return join(' ', $classes);
	}

	/**
	 * Render the button icon HTML.
	 *
	 * @return string
	 */
	public function renderIcon()
	{
		if (empty($this->icon))
		{
			return '';
		}

		return '<i class="fa fa-' . $this->icon . '"></i>';
	}

	/**
	 * Render the button HTML.
	 *
	 * @return string
	 */
	public function render()
	{
		$element = $this->elementTags[$this->element];

		$attributes = $this->attributes;

		$attributes['class'] = $this->getButtonClasses();

		if ($element == 'input')
		{
			$attributes['type'] = ($this->element == 'submit' ? 'submit' : 'button');
			$attributes['value'] = $this->text;
		}

		if ($this->disabled && ($this->element != 'link'))
		{
			$attributes['disabled'] = 'disabled';
		}

		if ($this->dropdown && !$this->splitDropdown)
		{
			$attributes['data-toggle'] = 'dropdown';
		}

		$attributes = $this->html->attributes($attributes);

		if ($element == 'input')
		{
			$html = ["<{$element}{$attributes} />"];
		}
		else
		{
			$html = [
				"<{$element}{$attributes}>",
				$this->renderIcon(),
				e($this->text),
				$this->body,
				$this->dropdown && !$this->splitDropdown ? '<span class="caret"></span>' : '',
				"</{$element}>",
			];
		}

		if (!empty($this->dropdown))
		{
			$this->wrapDropdown($html);
		}

		$button = join(PHP_EOL, $html);

		if ($this->centered)
		{
			$button = "<center>{$button}</center>";
		}

		return $button;
	}

	protected function wrapDropdown(&$html)
	{
		// prepend
		array_unshift($html, '<div class="btn-group' . ($this->dropup ? ' dropup' : '') . '">');

		// append
		$append = [];

		if ($this->splitDropdown)
		{
			$append[] = '<button type="button" class="' . $this->getButtonClasses() . '" data-toggle="dropdown">';
			$append[] = '<span class="caret"></span>';
			$append[] = '</button>';
		}

		$append[] = '<ul class="dropdown-menu">';

		foreach ($this->dropdown as $key => $value)
		{
			// "value" of each dropdown item can be a '|' separator...
			if ($value == '|')
			{
				$append[] = '<li role="separator" class="divider"></li>';
				continue;
			}
			// or the item text, where the key is the "href" attribute...
			elseif (!is_array($value))
			{
				$append[] = '<li><a href="' . $key . '">' . $value .'</a></li>';
				continue;
			}

			// or an array, where the 'el' key specifies the HTML tag (defaults to 'a'),
			// the 'text' keys specifies the innerHTML of the tag,
			// and the remaining keys other HTML tag attributes...
			$element = (isset($value['el']) ? $value['el'] : 'a');
			$text = (isset($value['text']) ? $value['text'] : '');
			$attributes = $this->html->attributes(array_except($value, ['el', 'text']));

			$append[] = "<li><{$element}{$attributes}>{$text}</{$element}></li>";
		}

		$append[] = '</ul>';
		$append[] = '</div>';

		array_splice($html, count($html), 0, $append);
	}

}
