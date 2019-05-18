<?php namespace Vocalogic;

class ToString {

	protected $toString;

	public function __construct($callable)
	{
		$this->toString = $callable;
	}

	public function __toString()
	{
		return call_user_func($this->toString);
	}

}
