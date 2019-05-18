<?php namespace Vocalogic\Http;

use ArrayAccess;

trait DispatchesCommands {

	/**
	 * Dispatch a command to its appropriate handler.
	 *
	 * @param  mixed  $command
	 * @return mixed
	 */
	protected function dispatch($command)
	{
		$result = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($command);

		return is_callable([$command, 'getResult']) ? $command->getResult() : $result;
	}

	/**
	 * Marshal a command and dispatch it to its appropriate handler.
	 *
	 * @param  mixed  $command
	 * @param  array  $array
	 * @return mixed
	 */
	protected function dispatchFromArray($command, array $array)
	{
		$result =  app('Illuminate\Contracts\Bus\Dispatcher')->dispatchFromArray($command, $array);

		return is_callable([$command, 'getResult']) ? $command->getResult() : $result;
	}

	/**
	 * Marshal a command and dispatch it to its appropriate handler.
	 *
	 * @param  mixed  $command
	 * @param  \ArrayAccess  $source
	 * @param  array  $extras
	 * @return mixed
	 */
	protected function dispatchFrom($command, ArrayAccess $source, $extras = [])
	{
		$result = app('Illuminate\Contracts\Bus\Dispatcher')->dispatchFrom($command, $source, $extras);

		return is_callable([$command, 'getResult']) ? $command->getResult() : $result;
	}

}
