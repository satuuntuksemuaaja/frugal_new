<?php namespace Vocalogic\Http;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests, AuthorizesRequests;

	/*
	 * Builds Redirect Response
	 *
	 * Specify either $destiny's "url" or "method" or "action" or "back" to set location
	 * Optionally include flash data with $destiny's "success" or "with"
	 */
	protected function redirect($destiny)
	{
		if (is_string($destiny))
		{
			$destiny = ['url' => $destiny];
		}

		if (isset($destiny['url']))
		{
			$redirect = redirect($destiny['url']);
		}
		elseif (isset($destiny['method']))
		{
			$params = (array) $destiny['method'];
			$params[0] = '\\' . get_class($this) . '@' . $params[0];
			$redirect = redirect();
			$redirect = call_user_func_array([$redirect, 'action'], $params);
		}
		elseif (isset($destiny['action']))
		{
			$params = (array) $destiny['action'];
			$redirect = redirect();
			$redirect = call_user_func_array([$redirect, 'action'], $params);
		}
		elseif (isset($destiny['back']))
		{
			$redirect = redirect()->back();

			if ($destiny['back'] == 'withInput')
			{
				$redirect->withInput();
			}
		}
		else
		{
			$redirect = redirect()->back();
		}

		if (isset($destiny['success']))
		{
			$redirect->with(['success' => $destiny['success']]);
		}

		if (isset($destiny['with']))
		{
			$redirect->with($destiny['with']);
		}

		return $redirect;
	}

	/*
	 * Builds Success Response
	 */
	protected function success($message, $redirect = [])
	{
		if (is_string($redirect))
		{
			$redirect = ['url' => $redirect];
		}

		$request = app()->make('request');

		if ($request->ajax() || $request->wantsJson())
		{
			$with = array_merge(['success' => $message], array_get($redirect, 'with', $redirect));

			return response()->json($with);
		}

		return $this->redirect(array_merge(['success' => $message], $redirect));
	}

}
