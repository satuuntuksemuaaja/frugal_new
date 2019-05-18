<?php

if ( ! function_exists('form'))
{
	/**
	 * Generate a form builder instance.
	 *
	 * @return Vocalogic\Html\FormBuilder
	 */
	function form()
	{
		return app('form')->init();
	}
}

if ( ! function_exists('user'))
{
	/**
	 * Get the authenticated user.
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	function user()
	{
		return app('auth')->user();
	}
}

if ( ! function_exists('at'))
{
	/**
	 * Match current route URI against provided regular expression.
	 *
	 * @return boolean
	 */
	function at($regex)
	{
		return (boolean) preg_match($regex, app('router')->current()->uri());
	}
}

if ( ! function_exists('is_current'))
{
	/**
	 * Match current route action (without namespace) against provided actions.
	 *
	 * @param  string|array $action
	 * @return boolean
	 */
	function is_current($action)
	{
		$current = last(explode('\\', app('router')->currentRouteAction()));

		return in_array($current, (array) $action);
	}
}

if ( ! function_exists('array_filled'))
{
	/**
	 * The array_filled method will return only the specified key / value pairs from the array,
	 * using a default value when the key does not exist on it (null if no $default is provided).
	 * The resulting array will always contain all keys
	 * (if $sorted is true, in the same order speficied in the keys array).
	 *
	 * @param  array  $array
	 * @param  array  $keys
	 * @param  mixed  $default
	 * @param  boolean  $sorted
	 * @return array
	 */
	function array_filled($array, $keys, $default = null, $sorted = false)
	{
		$keys         = array_values($keys);
		$array_keys   = array_fill_keys($keys, $default);
		$array_only   = array_only($array, $keys);
		$array_filled = $array_only + $array_keys;

		if ($sorted)
		{
			uksort($array_filled, function($a, $b) use($keys) {
				return array_search($a, $keys) > array_search($b, $keys);
			});
		}

		return $array_filled;
	}
}

if ( ! function_exists('build_where'))
{
	function build_where($conditions)
	{
		$where = array();
		foreach ($conditions as $condition)
		{
			if ($condition[1] == 'IN')
			{
				$where[] = $condition[0] . ' IN (' . build_values( $condition[2] ) . ')';
			}
			else
			{
				$where[] = $condition[0] . ' ' . $condition[1] . ' ' . esc_sql( $condition[2] );
			}
		}
		return empty($where) ? 'TRUE' : '(' . implode(') AND (', $where) . ')';
	}
}

if ( ! function_exists('build_values'))
{
	function build_values($values)
	{
		return implode(', ', array_map(create_function('$value', 'return esc_sql($value);'), $values));
	}
}

if ( ! function_exists('esc_sql'))
{
	function esc_sql($string)
	{
		return app('db')->getPdo()->quote($string);
	}
}

if ( ! function_exists('dispatch'))
{
	function dispatch($command)
	{
		$result = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($command);

		return is_callable([$command, 'getResult']) ? $command->getResult() : $result;
	}
}

if ( ! function_exists('curl'))
{
	function curl($curlOpts = [])
	{
		$ch = curl_init();
		foreach ($curlOpts as $curlOpt => $value)
		{
			curl_setopt($ch, $curlOpt, $value);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}

if ( ! function_exists('is_hhvm'))
{
	function is_hhvm()
	{
		return defined('HHVM_VERSION');
	}
}

if ( ! function_exists('toString'))
{
	function toString($callable)
	{
		return new Vocalogic\ToString($callable);
	}
}
