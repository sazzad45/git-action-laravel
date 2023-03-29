<?php


namespace App\Domain\FastPay;


use App\Domain\FastPay\API\FastPayOldApi;
use App\Domain\UserRelation\Models\User;
use Illuminate\Support\Facades\Cache;

class FastPay
{
    private ?string $auth_token;
    private User $user;
    private bool $hasException = false;
    private $response = null;
    private $api;

    public function __construct($user)
    {
        $this->hasException = false;
        $this->user = $user;
        $this->auth_token = $token = Cache::get($user->mobile_no.'_old_token');
    }

    public function callAPI(FastPayOldApi $api)
    {
        if($this->hasException) return $this;

        $this->api = $api;
        $this->api = $this->api->call($this->auth_token);
        $this->response = $this->api->getResponse();
        return $this;
    }

    public function getResponse()
    {
        if($this->response == null) {
            return response()->json([
                'code'      =>  500,
                'messages'  =>  ['Sorry! something went wrong. Our team is assessing the problem.'],
                'data'      =>  null
            ], 200);
        }

        return $this->response;
    }

    public function getData($type = "data")
    {
        if($type == "code"){
            return $this->api->getCode();
        }elseif($type == "message"){
            return $this->api->getMessage();
        }elseif($type == "data"){
            return $this->api->getData();
        }
    }
}
