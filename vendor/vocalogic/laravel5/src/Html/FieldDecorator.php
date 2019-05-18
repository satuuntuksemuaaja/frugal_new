<?php namespace Vocalogic\Html;

class FieldDecorator implements FieldDecoratorInterface {

	/*
	 * The rendered field attributes array
	 */
	public $field = [];

	/*
	 * Parsed field type
	 */
	public $type = '';

	/*
	 * The rendered field label HTML
	 */
	public $label = '';

	/*
	 * The rendered field input HTML
	 */
	public $input = '';

	/*
	 * Decoration attributes array
	 */
	public $decoration = [];

	/**
	 * Decorate the given input field
	 *
	 * @param  array   $field
	 * @param  string  $type
	 * @param  string  $label
	 * @param  string  $input
	 * @param  array   $decoration
	 * @return string
	 */
	public function decorate(array $field, $type, $label, $input, array $decoration)
	{
		$this->field = $field;
		$this->type = $type;
		$this->label = $label;
		$this->input = $input;
		$this->decoration = $decoration;

		return $this->handle();
	}

	/*
	 * Builds and returns the decorated field HTML
	 */
	public function handle()
	{
		$data = array_merge($this->decoration, [
			'field' => $this->field,
			'type'  => $this->type,
			'label' => $this->label,
			'input' => $this->input,
		]);

		return view($this->decoration['template'], $data);
	}

}
