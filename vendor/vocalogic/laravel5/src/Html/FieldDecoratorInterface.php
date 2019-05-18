<?php namespace Vocalogic\Html;

interface FieldDecoratorInterface {

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
	public function decorate(array $field, $type, $label, $input, array $decoration);

}
