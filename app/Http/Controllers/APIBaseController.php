<?php

namespace App\Http\Controllers;

use App\Traits\ActivityLogTrait;
use App\Traits\ApiResponseTrait;
use App\Traits\EnsureSecurityTrait;
use App\Traits\MiddlewareValidationTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class APIBaseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponseTrait, ActivityLogTrait, EnsureSecurityTrait;
}
