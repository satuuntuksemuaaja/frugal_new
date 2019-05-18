<?php namespace Vocalogic;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;


class ExceptionHandler extends Handler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		// Bugsnag support OOTB
		if (app()->bound('bugsnag') && $this->shouldReport($e) && empty($e->confidential))
		{
			app('bugsnag')->notifyException($e, null, "error");
		}

		// Strip stack trace from VocalogicException
		if (($e instanceof VocalogicException) && $this->shouldReport($e)
			&& !config('vocalogic.logVocalogicExceptionStackTrace', false))
		{
			$message = head(explode("\n", (string) $e));
			return $this->log->error($message);
		}

		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		if ($e instanceof VocalogicException)
		{
			return $this->renderVocalogicException($e);
		}

		if (($e instanceof ModelNotFoundException) && !config('app.debug'))
		{
			abort(404);
		}

		return parent::render($request, $e);
	}

	/**
	 * Create the response for VocalogicException.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  array  $errors
	 * @return \Illuminate\Http\Response
	 */
	protected function renderVocalogicException(VocalogicException $e)
	{
		$request = app()->make('request');

		if ($request->ajax() || $request->wantsJson())
		{
			return response()->json(['error' => $e->getMessage()], 422);
		}

		return redirect()->to($this->getRedirectUrl())
						->withInput($request->input())
						->withErrors($e->getErrors(), $e->getErrorBag());
	}

	/**
	 * Get the URL we should redirect to.
	 *
	 * @return string
	 */
	protected function getRedirectUrl()
	{
		return app('Illuminate\Routing\UrlGenerator')->previous();
	}

	 /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $e)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect()->guest('login');
        }
    }


}
