<?php

namespace App\Domain\UserRelation\Models;

use App\Constant\UserTypeId;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Level\Models\Level;
use App\Domain\Level\Models\LevelUser;
use App\Domain\Mandob\Models\MandobRating;
use App\Domain\Transaction\Models\FinancialService;
use App\Domain\Wallet\Models\MoneyRequest;
use App\Domain\Merchant\Models\BusinessProfile;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use HasApiTokens;

    protected $table = "users";

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pin',
        'fcm_key',
        'is_first_login'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAvatarAttribute()
    {
        $avatar = secure_asset("image/person.png");
        if (isset($this->profile_pic) && !empty($this->profile_pic)) {
            $avatar = $this->profile_pic;
        } elseif (isset($this->profile->photo) && !empty($this->profile->photo)) {
            $avatar = $this->profile->photo;
        }

        if ($this->user_type_id == UserTypeId::AGENT && !empty($this->agentProfile)
             && !empty($this->agentProfile->logo)) {
            $avatar = $this->agentProfile->logo;
        } elseif ($this->user_type_id == UserTypeId::MERCHANT && !empty($this->businessProfile)
             && !empty($this->businessProfile->bussiness_logo)) {
            $avatar = $this->businessProfile->bussiness_logo;
        }

        return $avatar;
    }

    public function getOriginalNameAttribute()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        if ($this->user_type_id == UserTypeId::AGENT && !empty($this->agentProfile)
             && !empty($this->agentProfile->business_name)) {
            $name = $this->agentProfile->business_name;
        } elseif ($this->user_type_id == UserTypeId::MERCHANT && !empty($this->businessProfile) 
             && !empty($this->businessProfile->bussiness_name)) {
            $name = $this->businessProfile->bussiness_name;
        }

        return $name;
    }

    public function userStatus()
    {
        return $this->belongsTo(UserStatus::class);
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class, 'business_id');
    }

    public function agentProfile()
    {
        return $this->hasOne(AgentProfile::class, 'agent_id');
    }

    public function accounts()
    {
        return $this->hasMany(UserAccount::class);
    }

    public function userSecurityQuestions()
    {
        return $this->hasMany(UserSecurityQuestion::class);
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function accountMigration()
    {
        return $this->hasMany(AccountMigration::class);
    }

    public function userVerificationDocs()
    {
        return $this->hasMany(UserVerificationDoc::class);
    }

    public function referralCodes()
    {
        return $this->hasMany(ReferralCode::class, 'owner_id');
    }

    public function financialServices()
    {
        return $this->belongsToMany(
            FinancialService::class,
            'agent_financial_service',
            'agent_id',
            'id'
        );
    }

    public function userRating()
    {
        return $this->hasOne(UserRating::class);
    }

    public function mandobRating()
    {
        return $this->hasMany(MandobRating::class);
    }

    public function moneyRequests()
    {
        return $this->hasMany(MoneyRequest::class, 'requestor_id');
    }

    public function requestedMoneyRequests()
    {
        return $this->hasMany(MoneyRequest::class, 'requestee_id');
    }

    public function mandobRequests()
    {
        return $this->hasMany(MoneyRequest::class, 'requestor_id')
            ->where('money_requests.type', 'mandob');
    }

    public function levels()
    {
        return $this->belongsToMany(Level::class, 'level_users');
    }

    public function level()
    {
        return $this->hasOne(LevelUser::class, 'user_id')->where('status', 1);
    }

    public function userDevices()
    {
        return $this->hasMany(UserDevice::class);
    }
}
