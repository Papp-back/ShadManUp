<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    
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
        'login'
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
}