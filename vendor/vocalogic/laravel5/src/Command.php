<?php namespace Vocalogic;

/*
 * This trait provides a standard constructor
 * and a way to return results from commands.
 */

trait Command {

	/*
	 * Command parameters
	 */
	private $_parameters = array();

	/*
	 * Stores command result
	 */
	protected $_result;

	/**
	 * Create a new command instance.
	 *
	 * @return  Vocalogic\Command
	 */
	public function __construct()
	{
		// We will collect all arguments in this array
		$args = [];

		// First, we will loop through all arguments passed to the constructor
		foreach (func_get_args() as $arg)
		{
			// If argument is an array, let's merge it into our $args
			if (is_array($arg))
			{
				$args = array_merge($args, $arg);
			}
			// If it has an "all" method (a Collection), let's merge it into our $args
			elseif (is_callable([$arg, 'all']))
			{
				$args = array_merge($args, $arg->all());
			}
			// If it has a "getArrayCopy" method (an ArrayObject), let's merge it into our $args
			elseif (is_callable([$arg, 'getArrayCopy']))
			{
				$args = array_merge($args, $arg->getArrayCopy());
			}
			// Anything else (any object) will be converted into an array, and merged into our $args
			else
			{
				$args = array_merge($args, (array) $arg);
			}

			/*
			 * Note that later arguments may override values from previous arguments!
			 *
			 * You can use this behaviour passing defaults first,
			 * as well as passing request before any values you wants to enforce/ensure.
			 *
			 * Example:
			 *
			 * $defaults = ['position': top];
			 * $request = (the Request object);
			 * $overrides = ['user' => Auth::user()];
			 * new Command($defaults, $request, $overrides);
			 *
			 * In this example,
			 * 'position' may be modified by the request or not
			 * but 'user' cannot be modified by the request.
			 */
		}

		// The command class using this trait define its parameters through its declared properties
		$parameters = array_keys(call_user_func('get_object_vars', $this));

		// Now we will populate the parameters with the arguments
		foreach ($parameters as $parameter)
		{
			// If the parameter is present in the arguments, assign its value
			if (isset($args[$parameter]))
			{
				$this->$parameter = $args[$parameter];

				// We will also store the original parameters received through the arguments in an array
				// which can be retrieved with ease at any later time (see getParameters below)
				$this->_parameters[$parameter] = $this->$parameter;
			}
		}
	}

	/**
	 * Get command parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_parameters;
	}

	/**
	 * Informs if a specific parameter has been provided.
	 *
	 * @param  string  $param
	 * @return boolean
	 */
	public function hasParameter($param)
	{
		return isset($this->_parameters[$param]);
	}

	/**
	 * Set command result.
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function setResult($value)
	{
		$this->_result = $value;
	}

	/**
	 * Get command result.
	 *
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->_result;
	}

}
