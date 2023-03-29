<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use App\Traits\FeatureAccessTrait;
use Closure;

class AuthorizedToDoCustomerKyc
{
    use ApiResponseTrait, FeatureAccessTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->ableToDoCustomerKyc(auth()->user())) {
            return $this->unauthorizedResponse([trans('messages.unauthorized_access')], 422);
        }

        return $next($request);
    }
}
