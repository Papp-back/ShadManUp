<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Traits\JalaliDateTrait;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable,JalaliDateTrait;
    protected $appends = ['total_course_payments'];
    protected $fillable = [
        'avatar',
        'referral',
        'cellphone',
        'email',
        'email_verified_at',
        'firstname',
        'national_code',
        'lastname',
        'wallet',
        'wallet_expire',
        'wallet_gift',
        'password',
        'phone_code',
        'phone_code_send_time',
        'role',
        'referrer',
        'ref_level',
        'login_level',
        'login',
        'born_at'
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getAuthIdentifierName()
    {
        return 'id';
    }
    function getAuthPassword(){
        if ($this->password) {
            return Hash::make($this->password);
        }
        
      }
    public function getJWTCustomClaims()
    {
        return [];
    }


    /**
     * Generate a unique referral code for the user.
     *
     * @return string
     */
    public static function generateReferralCode()
    {
        $code = strtoupper(Str::random(6)); // Generate a random string (e.g., 'ABC123')
        // Check if the generated code already exists in the database
        while (User::where('referral', $code)->exists()) {
            $code = strtoupper(Str::random(6)); // Regenerate the code if it already exists
        }
        return $code;
    }

    /**
     * Boot function for the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        // Generate a referral code when creating a new user
        static::creating(function ($user) {
            $user->referral = self::generateReferralCode();
        });
    }

    /**
     * Get the referrer (parent) of the user.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referral');
    }
    /**
     * Get the referrals (children) of the user.
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referral');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
     /**
     * Check if the referrer of the user exists.
     */
    public function hasReferrer()
    {
        return !is_null($this->referrer);
    }

    /**
     * Get the referral level of the user.
     */
    public function getReferralLevel()
    {
        $level = 0;
        $referrerCode = $this->referrer;
        while (!is_null($referrerCode)) {
            $referrer = User::where('referral', $referrerCode)->first();
            if (!$referrer) {
                break; // Exit loop if referrer not found
            }
            $level++;
            $referrerCode = $referrer->referrer; // Update referrer code for the next iteration
        }
        return $level;
    }
    public function withJdate()
    {
        $array = parent::toArray();

        $array['jcreated_at'] = $this->getJCreatedAtAttribute();
        $array['jupdated_at'] = $this->getJUpdatedAtAttribute();
        $array['jborn_at'] = $this->getJBornAtAttribute();

        return $array;
    }
    public function withJdateHuman()
    {
        $array = parent::toArray();
        $array['jcreated_at'] = $this->getJCreatedAtAttribute();
        $array['jupdated_at'] = $this->getJUpdatedAtAttribute();
        $array['jborn_at'] = $this->getJBornAtAttribute();
        $array['created_at_for_humans'] = $this->getCreatedAtForHumansAttribute();
        $array['updated_at_for_humans'] = $this->getUpdatedAtForHumansAttribute();
        $array['deleted_at_for_humans'] = $this->getDeletedAtForHumansAttribute();
        $array['wallet_prettified'] = number_format(floatval($this->wallet), 0, '.', ',');
        $array['wallet_gift_prettified'] =number_format(floatval($this->wallet_gift), 0, '.', ',');
        $array['total_course_payments_prettified'] =number_format(floatval($this->total_course_payments), 0, '.', ',');
       

        return $array;
    }
    public function prettifyPriceWallet()
    {
        $array = parent::toArray();
        $array['wallet_prettified'] = number_format(floatval($this->wallet), 0, '.', ',');
        $array['wallet_gift'] =number_format(floatval($this->wallet_gift), 0, '.', ',');
        return $array;
    }
    public function getTotalCoursePaymentsAttribute()
    {
        return $this->coursePayments()->sum('Amount');
    }
    public function coursePayments()
    {
        return $this->hasMany(Payment::class)->where('payments.pay', 1)->where(function ($q) {
            $q->where('paytype','section');
            $q->orWhere('paytype','course');
            
        })
        ;
    }
    public function comments()
    {
        return $this->hasMany(CourseComment::class);
    }
}