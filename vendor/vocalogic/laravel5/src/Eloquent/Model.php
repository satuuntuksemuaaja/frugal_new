<?php namespace Vocalogic\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\QueryException;
use Vocalogic\VocalogicException;

class Model extends EloquentModel {

	use ValidatesModels;

	// https://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html
	const DUPLICATED_KEY = 1062;
	const CANNOT_DELETE  = 1451;
	const CANNOT_SAVE    = 1452;

	public function save(array $options = [])
	{
		try
		{
			parent::save($options);
		}
		catch (QueryException $e)
		{
			throw $this->vocalogicExceptionIf([self::DUPLICATED_KEY, self::CANNOT_SAVE], $e);
		}
	}

	public function delete()
	{
		try
		{
			parent::delete();
		}
		catch (QueryException $e)
		{
			throw $this->vocalogicExceptionIf(self::CANNOT_DELETE, $e);
		}
	}

	protected function vocalogicExceptionIf($mySqlErrorNumber, QueryException $e)
	{
		if ( ! $this->isIntegrityViolation($mySqlErrorNumber, $e, $actualErrorNumber))
		{
			return $e;
		}

		switch ($actualErrorNumber)
		{
			case self::DUPLICATED_KEY:
				return new VocalogicException($this->getDuplicatedKeyErrorMessage());

			case self::CANNOT_DELETE:
				return new VocalogicException($this->getCannotDeleteErrorMessage());

			case self::CANNOT_SAVE:
				return new VocalogicException($this->getCannotSaveErrorMessage($e));
		}
	}

	protected function isIntegrityViolation($mySqlErrorNumber, QueryException $e, &$actualErrorNumber = null)
	{
		$message = $e->getMessage();

		if ( ! preg_match('/Integrity constraint violation:/', $message))
		{
			return false;
		}

		/**
		 * We are using a HHVM bug workaround here... "$e->errorInfo" should work.
		 * https://github.com/facebook/hhvm/issues/4003
		 */
		list($sqlState, $actualErrorNumber) = (is_hhvm() ? \DB::getPdo()->errorInfo() : $e->errorInfo);

		return (($sqlState == 23000) && in_array($actualErrorNumber, (array) $mySqlErrorNumber));
	}

	protected function getDuplicatedKeyErrorMessage()
	{
		return isset($this->messages['unique']) ? $this->messages['unique'] : 'Another record with similar properties already exists.';
	}

	protected function getCannotDeleteErrorMessage()
	{
		return isset($this->messages['busy']) ? $this->messages['busy'] : 'This cannot be deleted because it is currently being used.';
	}

	protected function getCannotSaveErrorMessage(QueryException $e)
	{
		$invalidField = '';
		if (preg_match('/FOREIGN KEY \((\`[^\`]+\`)\)/', $e->getMessage(), $matches))
		{
			$invalidField = ' Invalid value for ' . $matches[1] . '.';
		}
		return isset($this->messages['integrity']) ? $this->messages['integrity'] : 'This cannot be saved.' . $invalidField;
	}

}
