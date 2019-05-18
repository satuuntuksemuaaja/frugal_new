<?php namespace Vocalogic;

use RuntimeException;

class VocalogicException extends RuntimeException {

	public function getErrors()
	{
		if (isset($this->validator))
		{
			return $this->validator;
		}

		return $this->getMessage();
	}

	/**
	 * Get the key to be used for the view error bag.
	 *
	 * @return string
	 */
	public function getErrorBag()
	{
		return 'default';
	}

}
