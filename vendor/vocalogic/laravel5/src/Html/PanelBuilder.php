<?php namespace Vocalogic\Html;

class PanelBuilder {

	use GenericBuilder;

	protected $context = 'default';

	protected $header = [];

	protected $body = [];

	protected $table = [];

	protected $listGroup = [];

	protected $footer = [];

	/**
	 * Set the panel context. (default, primary, success, info, warning or danger)
	 *
	 * @param  string  $context
	 * @return Vocalogic\Html\PanelBuilder
	 */
	public function context($context)
	{
		$this->context = $context;
		return $this;
	}

	/**
	 * Set the panel header.
	 *
	 * @param  string  $contents
	 * @param  string  $more
	 * @param  array   $attributes
	 * @param  string  $contentWrapper
	 * @param  string  $moreWrapper
	 * @return Vocalogic\Html\PanelBuilder
	 */
	public function header($contents, $more = '', array $attributes = [],
	                       $contentsWrapper = '<h3 class="panel-title">%s</h3>',
	                       $moreWrapper = '<span class="pull-right">%s</span>')
	{
		$this->header = [
			'contents'   => $contents,
			'more'       => $more,
			'attributes' => $attributes,
			'contentsWrapper' => $contentsWrapper,
			'moreWrapper'     => $moreWrapper,
		];
		return $this;
	}

	/**
	 * Set the panel body.
	 *
	 * @param  string   $contents
	 * @param  boolean  $withPanelBody
	 * @param  array    $attributes
	 * @return Vocalogic\Html\PanelBuilder
	 */
	public function body($contents, $withPanelBody = true, array $attributes = [])
	{
		$this->body = [
			'contents'      => $contents,
			'withPanelBody' => $withPanelBody,
			'attributes'    => $attributes,
		];
		return $this;
	}

	/**
	 * Set the panel table.
	 *
	 * @param  string   $contents
	 * @param  boolean  $beforeBody
	 * @return Vocalogic\Html\PanelBuilder
	 */
	public function table($contents, $beforeBody = false)
	{
		$this->table = [
			'contents'   => $contents,
			'beforeBody' => $beforeBody,
		];
		return $this;
	}

	/**
	 * Set the panel list group.
	 *
	 * @param  string   $contents
	 * @param  boolean  $beforeBody
	 * @return Vocalogic\Html\PanelBuilder
	 */
	public function listGroup($contents, $beforeBody = false)
	{
		$this->listGroup = [
			'contents'   => $contents,
			'beforeBody' => $beforeBody,
		];
		return $this;
	}

	/**
	 * Set the panel footer.
	 *
	 * @param  string   $contents
	 * @param  boolean  $rightAlign
	 * @param  array    $attributes
	 * @return Vocalogic\Html\PanelBuilder
	 */
	public function footer($contents, $rightAlign = false, array $attributes = [])
	{
		$this->footer = [
			'contents'   => $contents,
			'rightAlign' => $rightAlign,
			'attributes' => $attributes,
		];
		return $this;
	}

	/**
	 * Render the full HTML panel.
	 *
	 * @return string
	 */
	public function render()
	{
		$attributes = $this->attributes;

		if (!isset($attributes['class']))
		{
			$attributes['class'] = 'panel panel-' . $this->context;
		}
		else
		{
			$attributes['class'] = 'panel panel-' . $this->context . ' ' . $attributes['class'];
		}

		$attributes = $this->html->attributes($attributes);

		$html = [
			"<div{$attributes}>",
			$this->renderHeading(),
			$this->renderBody(),
			$this->renderFooter(),
			"</div>",
		];

		return join(PHP_EOL, $html);
	}

	/**
	 * Render the panel heading HTML.
	 *
	 * @return string
	 */
	public function renderHeading()
	{
		if (empty($this->header['contents']))
		{
			return '';
		}

		/// Attributes ///
		$attributes = (empty($this->header['attributes']) ? [] : $this->header['attributes']);

		if (!isset($attributes['class']))
		{
			$attributes['class'] = 'panel-heading';
		}
		else
		{
			$attributes['class'] = 'panel-heading ' . $attributes['class'];
		}

		$attributes = $this->html->attributes($attributes);

		/// Contents ///
		$contents = $this->header['contents'];

		if (!empty($this->header['contentsWrapper']))
		{
			$contents = sprintf($this->header['contentsWrapper'], $contents);
		}

		/// More ///
		$more = (empty($this->header['more']) ? '' : $this->header['more']);

		if (!empty($more) && !empty($this->header['moreWrapper']))
		{
			$more = sprintf($this->header['moreWrapper'], $more);
		}

		$html = [
			"<div{$attributes}>",
			$contents . $more,
			'</div>',
		];

		return join(PHP_EOL, $html);
	}

	/**
	 * Render the panel body HTML.
	 *
	 * @return string
	 */
	public function renderBody()
	{
		if (empty($this->body['contents']) && empty($this->table['contents']) && empty($this->listGroup['contents']))
		{
			return '';
		}

		/// Contents ///
		$html = (empty($this->body['contents']) ? '' : $this->body['contents']);

		if (!empty($html) && !empty($this->body['withPanelBody']))
		{
			/// Attributes ///
			$attributes = (empty($this->body['attributes']) ? [] : $this->body['attributes']);

			if (!isset($attributes['class']))
			{
				$attributes['class'] = 'panel-body';
			}
			else
			{
				$attributes['class'] = 'panel-body ' . $attributes['class'];
			}

			$attributes = $this->html->attributes($attributes);

			$html = sprintf("<div{$attributes}>%s</div>", $html);
		}

		/// Table ///
		if (!empty($this->table['contents']))
		{
			$html = (empty($this->table['beforeBody']) ? $html . $this->table['contents'] : $this->table['contents'] . $html);
		}

		/// List Group ///
		if (!empty($this->listGroup['contents']))
		{
			$html = (empty($this->listGroup['beforeBody']) ? $html . $this->listGroup['contents'] : $this->listGroup['contents'] . $html);
		}

		return $html;
	}

	/**
	 * Render the panel footer HTML.
	 *
	 * @return string
	 */
	public function renderFooter()
	{
		if (empty($this->footer['contents']))
		{
			return '';
		}

		/// Attributes ///
		$attributes = (empty($this->footer['attributes']) ? [] : $this->footer['attributes']);

		if (!isset($attributes['class']))
		{
			$attributes['class'] = 'panel-footer';
		}
		else
		{
			$attributes['class'] = 'panel-footer ' . $attributes['class'];
		}

		$attributes = $this->html->attributes($attributes);

		/// Contents ///
		$contents = $this->footer['contents'];

		if (!empty($this->footer['alignRight']))
		{
			$contents = sprintf('<span class="pull-right">%s</span>', $contents);
		}

		$html = [
			"<div{$attributes}>",
			$contents,
			'</div>',
		];

		return join(PHP_EOL, $html);
	}

}
