<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;

class CheckUserTypeIsAgent
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (
            $request->user() &&
            $request->user()->userType->name != 'Agent'
        ) {
            return $this->unauthorizedResponse(["You are unauthorized to access this area."]);
        }

        return $next($request);
    }
}
