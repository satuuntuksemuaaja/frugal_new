<?php namespace Vocalogic\Eloquent;

use Vocalogic\VocalogicException;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Validation\Validator;

class ValidateModelException extends VocalogicException {

	/**
	 * The underlying model instance.
	 *
	 * @var \Illuminate\Database\Eloquent\Model
	 */
	protected $model;

	/**
	 * The underlying validator instance.
	 *
	 * @var \Illuminate\Validation\Validator
	 */
	protected $validator;

	/**
	 * Create a new validate model exception instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  \Illuminate\Validation\Validator  $validator
	 * @return void
	 */
	public function __construct(EloquentModel $model, Validator $validator)
	{
		parent::__construct($validator->messages()->first(), 9000);

		$this->model = $model;

		$this->validator = $validator;
	}

	/**
	 * Get the underlying model instance.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Get the underlying validator instance.
	 *
	 * @return \Illuminate\Validation\Validator
	 */
	public function getValidator()
	{
		return $this->validator;
	}

}
