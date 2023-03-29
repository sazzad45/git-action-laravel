<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Laravel\Passport\Exceptions\OAuthServerException::class,
        OAuthServerException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        if ($exception instanceof OAuthServerException){
            Log::error("Malformed or Invalid Token Received");
            return;
        }

        Log::critical($exception->getFile() . ' ' . $exception->getLine() . ' ' . $exception->getMessage());
        Log::info("Exception for Endpoint (in agent): ".request()->fullUrl());
        Log::info((array)request()->except(['password', 'pin']));

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {

        if($exception instanceof ThrottleRequestsException)
        {
            return response()->json([
                'code' => 429,
                'messages' => ['Too Many Attempts. Please Slow down your request.'],
                'data' => null
            ], 200);
        }


        return parent::render($request, $exception);
    }


    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse|JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            //return response()->json(['error' => 'Unauthenticated'], 401);
            return response()->json([
                'code' => 401,
                'messages' => ['Authentication failed'],
                'data' => null
            ], 401);
        }
        return redirect()->guest(route('login'));
    }
}
