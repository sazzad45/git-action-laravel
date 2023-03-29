<?php

namespace App\Http\Middleware;

use App\Domain\UserRelation\Models\User;
use App\Traits\EnsureSecurityTrait;
use Closure;

class IsUserBlocked
{
    use EnsureSecurityTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $action)
    {
        $mobile_no = auth()->check() ?
            $request->user()->mobile_no :
            $request->mobile_number;

        if ( ! $mobile_no ) {
            if ($request->filled('email')) {
                $mobile_no = User::where('email', $request->email)->first()->mobile_no ?? '';
            }
        }

        if ($mobile_no) {
            if ($block = $this->isBlocked($mobile_no, $action)) {
                return $this->blockedPublicApiResponse($request, $block->remarks);
            }
        }

        return $next($request);
    }
}
