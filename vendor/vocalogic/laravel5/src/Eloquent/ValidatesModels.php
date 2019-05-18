<?php namespace Vocalogic\Eloquent;

use Illuminate\Validation\Validator;

trait ValidatesModels {

	protected $rules = array();

	protected $messages = array();

	/**
	 * Validate the given model with its own rules.
	 *
	 * @param  array  $rules
	 * @param  array  $messages
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function validate(array $rules = array(), array $messages = array())
	{
		$rules = array_merge($this->rules, $rules);

		$messages = array_merge($this->messages, $messages);

		$validator = $this->getValidationFactory()->make($this->getAttributes(), $rules, $messages);

		if ($validator->fails())
		{
			$this->throwValidationException($validator);
		}

		return $this;
	}

	/**
	 * Throw the failed validation exception.
	 *
	 * @param  \Illuminate\Contracts\Validation\Validator  $validator
	 * @return void
	 */
	protected function throwValidationException($validator)
	{
		throw new ValidateModelException($this, $validator);
	}

	/**
	 * Get a validation factory instance.
	 *
	 * @return \Illuminate\Contracts\Validation\Factory
	 */
	protected function getValidationFactory()
	{
		return app('Illuminate\Contracts\Validation\Factory');
	}

}
