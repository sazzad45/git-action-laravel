<?php

namespace App\Http\Middleware;

use App\Constant\UserStatusId;
use App\Traits\ApiResponseTrait;
use App\Traits\EnsureSecurityTrait;
use Closure;
use Illuminate\Support\Facades\Log;

class UserHasAccess
{
    use EnsureSecurityTrait;
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {


            $user = auth()->user();

            if ($user->status == 0) {

                $request->user()->token()->revoke();

                return response()->json([
                    'messages' => ["Account is currently disable. Wait for sometime or contact with call center. Thanks"],
                    'data' => [],
                    'code' => 403
                ], 200);
            }

            if ($user->user_status_id == UserStatusId::TEMPORARY_BLOCKED) {

                $request->user()->token()->revoke();

                return response()->json([
                    'messages' => ["Account is temporary blocked. Wait for sometime or contact with call center. Thanks"],
                    'data' => [],
                    'code' => 403
                ], 200);
            }

            if ($user->user_status_id == UserStatusId::PERMANENTLY_CLOSED) {

                $request->user()->token()->revoke();

                return response()->json([
                    'messages' => ["Account is permanently closed. Contact with call center for details. Thanks"],
                    'data' => [],
                    'code' => 403
                ], 200);
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
