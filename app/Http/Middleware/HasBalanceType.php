<?php

namespace App\Http\Middleware;

use App\Constant\BalanceType;
use App\Traits\ApiResponseTrait;
use Closure;

class HasBalanceType
{
    use ApiResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $action)
    {
        $user = auth()->user();
        $user->load('agentProfile');

        $balanceType = $user->agentProfile->system_type ?? BalanceType::FASTPAY;

        if($balanceType != BalanceType::BOTH && $balanceType != $action)
        {
            return $this->unauthorizedResponse(["You are unauthorized to access this area."]);
        }

        return $next($request);
    }
}
