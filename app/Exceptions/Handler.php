<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Debug\Exception\FlattenException;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if (!$e instanceof FlattenException) {
            $e = FlattenException::create($e);
        }

        return new JsonResponse([
            'code'=>$e->getStatusCode(),
            'status'=>'error',
            'message'=>$e->getMessage(),
            'trace'=>env('APP_ENV') == "local" ? $e->getTrace() : ["trace disabled"],
        ], $e->getStatusCode(), $e->getHeaders());
    }

}
