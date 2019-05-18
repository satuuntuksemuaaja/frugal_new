<?php namespace Vocalogic\Html;

class FormField
{
	public static $types = [
		'text'     => 'Text Input',
		'select'   => 'Dropdown Menu',
		'checkbox' => 'Checkbox',
		'textarea' => 'Text Area',
	];

	public static function schema($t)
	{
		$t->string('field_name');         // Name of the field
		$t->string('field_type');         // One of FormField::$types
		$t->text('field_default');        // Default value for the field
		$t->text('field_description');    // Description of the field
		$t->boolean('field_required');    // Required or not
		$t->text('field_meta');           // Used for select boxes, checkboxes, etc.
	}

	public static function getTypeOpts($value = null)
	{
		$opts = static::$types;
		return (in_array($value, array_keys($opts)) ? [] : ['--- Select ---']) + $opts;
	}

	public static function getField($model, $value = null)
	{
		$field = [
			'name'      => $model->field_name,
			'type'      => $model->field_type,
			'val'       => empty($value) ? $model->field_default : $value,
			'_comment'  => $model->field_description,
			'_required' => $model->field_required,
		];

		$field = array_merge($field, (array) json_decode($model->field_meta, true));

		return $field;
	}
}
