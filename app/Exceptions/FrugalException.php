<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 8:20 PM
 */

namespace FK3\Exceptions;


use Exception;
use Throwable;

class FrugalException extends Exception
{

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        if ($request->headers->get('X-Requested-With') == 'XMLHttpRequest')
            return response()->json([
                'error' => $this->getMessage()
            ]);
        else return redirect()->back()->withError($this->getMessage());

        return redirect()->to('error')->withMessage($this->getMessage());
        return parent::render($request, $exception);
    }

}