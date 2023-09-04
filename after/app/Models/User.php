<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function mutations() {
        return $this->hasMany(Transaction::class);
    }

    function createTransaction($transType = "IN", $amount) {
        $createTransaction = new Transaction();
        $createTransaction->user_id = $this->id;
        $createTransaction->amount = $amount;
        $createTransaction->transaction_type = $transType;
        $createTransaction->save();

        if($transType == "IN") {
            $this->balance += $amount;
        } else {
            $this->balance -= $amount;
        }

        $this->save();

        return $this;
    }
}
