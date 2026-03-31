<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Friend;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom_complet',
        'username',
        'password',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
                    ->wherePivot('status', 'accepted')
                    ->withTimestamps();
    }

    public function friendRequests()
    {
        return $this->hasMany(Friend::class, 'friend_id')->where('status', 'pending');
    }

    public function allFriends(){
        $friendIds = Friend::where('status', 'accepted')
            ->where(function($q) {
                $q->where('user_id', $this->id)
                ->orWhere('friend_id', $this->id);
            })
            ->get()
            ->map(function($f) {
                return $f->user_id == $this->id ? $f->friend_id : $f->user_id;
            })
            ->toArray();

        return User::whereIn('id', $friendIds)->get();
    }

    public function blockedFriends(){
        $userId = $this->id;

        $blockedIds = Friend::where('status', 'blocked')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                ->orWhere('friend_id', $userId);
            })
            ->get()
            ->map(function($f) use ($userId) {
                return $f->user_id == $userId
                    ? $f->friend_id
                    : $f->user_id;
            })
            ->toArray();

        return User::whereIn('id', $blockedIds)->get();
    }

    public function generateApiToken()
    {
        $this->api_token = Str::random(60);
        $this->save();
        return $this->api_token;
    }

    public static function findByApiToken(?string $token)
    {
        if (!$token) return null;
        return self::where('api_token', $token)->first();
    }
}
