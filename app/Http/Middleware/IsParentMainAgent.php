<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use App\Traits\EnsureSecurityTrait;
use App\Traits\FeatureAccessTrait;
use Closure;

class IsParentMainAgent
{
    use FeatureAccessTrait;
    use EnsureSecurityTrait;
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
        if ( ! $this->isParentMainAgent($request->user()) ) {
            return $this->unauthorizedResponse([trans('messages.unauthorized')], 422);
        }

        return $next($request);
    }
}
