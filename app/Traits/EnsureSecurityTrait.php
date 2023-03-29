<?php

namespace App\Traits;


use App\Constant\Security\BlockReason;
use App\Domain\API\Utility\OTPPurpose;
use App\Domain\Independent\Models\OTP;
use App\Domain\Security\BlockList;
use App\Domain\UserRelation\Models\User;
use App\Domain\Security\IncomingRequest;
use App\Domain\Security\UserAction;
use App\Notifications\AccountLocked;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait EnsureSecurityTrait
{
    protected function blockNow($mobileNumber, $minutesToBlock, $action, $remarks)
    {
        $blockList = new BlockList();
        $blockList->mobile_no = $mobileNumber;
        $blockList->action = $action;
        $blockList->remarks = $remarks;
        $blockList->blocked_at = Carbon::now()->format('Y-m-d H:i:s');
        $blockList->unblock_at = Carbon::now()->addMinutes($minutesToBlock)->format('Y-m-d H:i:s');
        $blockList->audit = json_encode($this->getAudit());
        $blockList->save();
    }

    private function getAudit()
    {
        return [
            'app' => config('basic_settings.app_name'),
            'ip' => trim(explode(',', request()->header('X-Forwarded-For'))[0]),
            'api_url' => request()->url(),
            'method' => request()->method(),
        ];
    }

    protected function isBlocked($mobileNo, $action)
    {
        $blockList = BlockList::whereMobileNo($mobileNo)->whereAction($action)->latest()->first();

        if($blockList) {
            $now = Carbon::now()->timestamp;

            if ($blockList->blocked_at && $blockList->unblock_at && $now > strtotime($blockList->unblock_at)) {
                return false;
            }

            return $blockList;
        }

        return false;
    }

    protected function blockedApiResponse(Request $request, $remarks)
    {
        return response()->json([
            'messages'  =>  [ trans('messages.feature_block', ['remarks' => $remarks, 'contact_no' => config('basic_settings.company.mobile_no')]) ],
            'data'      =>  [],
            'code'      =>  422
        ], 200);
    }

    protected function blockedPublicApiResponse(Request $request, $reason)
    {
        return response()->json([
            'messages'  =>  [ trans('messages.feature_block', ['remarks' => $reason, 'contact_no' => config('basic_settings.company.mobile_no')]) ],
            'data'      =>  null,
            'code'      =>  403
        ], 200);
    }


    protected function logRequest($mobileNo, $payload = null, $feature, $ipAddress, $status = 1)
    {
        IncomingRequest::create([
            'mobile_no'     =>  $mobileNo,
            'payload'       =>  $payload,
            'feature'       =>  $feature,
            'ip_address'    =>  $ipAddress,
            'status'        =>  $status
        ]);
    }


    protected function checkIfAttemptLimitExceeds($mobileNumber, $feature, $attemptLimit, $minutesOfInterval)
    {
        $maxNoOfAttempt = IncomingRequest::whereMobileNo($mobileNumber)
            ->whereFeature($feature)->whereStatus(false)
            ->whereRaw("TIMESTAMPDIFF(MINUTE, created_at, NOW()) < $minutesOfInterval")
            ->count();

        if($maxNoOfAttempt >= $attemptLimit) {
            return true;
        }

        return false;
    }

    protected function userActionDailyLimitCrossed(User $user, $ipAddress, $action, $addition = 0)
    {
        $dailyIpAddrActionCount = UserAction::where('ip_address', $ipAddress)
            ->where('action', $action)
            ->whereBetween('created_at', [
                Carbon::today()->startOfDay()->format('Y-m-d H:i:s'),
                Carbon::today()->endOfDay()->format('Y-m-d H:i:s')
            ])
            ->count();

        if ($dailyIpAddrActionCount >= config('user_action_limit.forget_password.same_ip_addr_daily_limit') + $addition) {
            return response()->json([
                'messages'  =>  [ trans('messages.user_action_daily_limit_crossed', ['action' => $action]) ],
                'data'      =>  null,
                'code'      =>  403
            ], 200);
        }

        $dailyUserActionCount = UserAction::where('user_id', $user->id)
            ->where('action', $action)
            ->whereBetween('created_at', [
                Carbon::today()->startOfDay()->format('Y-m-d H:i:s'),
                Carbon::today()->endOfDay()->format('Y-m-d H:i:s')
            ])
            ->count();

        if ($dailyUserActionCount >= config('user_action_limit.forget_password.same_user_daily_limit') + $addition) {
            return response()->json([
                'messages'  =>  [ trans('messages.user_action_daily_limit_crossed', ['action' => $action]) ],
                'data'      =>  null,
                'code'      =>  403
            ], 200);
        }

        return false;
    }

//    protected function isOtpGenerationLimitExceeded(Request $request, $action, $purpose, $blockSummary)
//    {
//        if($block = $this->isBlocked($request->input('mobile_no'), $action)) {
//            return $this->blockedPublicApiResponse($request, $action);
//        }
//
//        if(OtpArchive::whereMobileNo($request->input('mobile_no'))->wherePurpose($purpose)->count() > 5) {
//            $this->blockNow($request->input('mobile_no'), 30 * 24 * 60, $action, $blockSummary);
//            return $this->blockedPublicApiResponse($request, $action);
//        }
//
//        return false;
//    }
//
//    protected function isOtpGenerationLimitExceededInBetween(Request $request, $action, $purpose, $blockSummary, $maxLimit, $withinMinutes)
//    {
//        $isApiVersionV1 = false;
//
//        if($block = $this->isBlocked($request->user()->mobile_no, $action)) {
//            return ($isApiVersionV1) ? $this->blockedApiResponse($request, $action) : $this->blockedPublicApiResponse($request, $action);
//        }
//
//        if(OtpArchive::whereMobileNo($request->user()->mobile_no)
//                ->wherePurpose($purpose)
//                ->whereRaw("created_at > date_sub(now(), interval {$withinMinutes} minute)")
//                ->count() >= $maxLimit) {
//            $this->blockNow($request->user()->mobile_no, 24 * 60, $action, $blockSummary);
//
//            return ($isApiVersionV1) ? $this->blockedApiResponse($request, $action) : $this->blockedPublicApiResponse($request, $blockSummary);
//        }
//
//        return false;
//    }

    private function OTPMaxUsedBlockCheck($user, $email, $mobile_number, $purpose = OTPPurpose::PASSWORD_RESET)
    {
        $date = new \DateTime;
        $to_date = $date->format('Y-m-d H:i:s');
        $date->modify('-5 minutes');
        $from_date = $date->format('Y-m-d H:i:s');

        $otps = OTP::where('identity', $email ?? $mobile_number)
            ->where('purpose', $purpose)
            ->where('status', 0)
            ->where('created_at', ">=", Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'))
            ->where('created_at', '>=', $from_date)
            ->where('created_at', '<=', $to_date)
            ->orderBy('created_at', 'DESC')
            ->get();

        $countUnusedOTP = 0;

        foreach($otps as $otp)
        {
            if($otp->status == 0){
                $countUnusedOTP++;
            }

            if($countUnusedOTP == 5){
                break;
            }

            if($otp->status == 1){
                break;
            }

        }


        if ($countUnusedOTP >= 5) {
            $this->blockNow(
                $user->mobile_no,
                60,
                BlockReason::Too_Many_OTP_Generated,
                'Five(5) failed OTP Generated in last five(5) minutes'
            );

            if ($user->email_verified) {
                $user->notify(new AccountLocked($user->name, 'multiple failed OTP verification attempts'));
            }
        }
    }

}
