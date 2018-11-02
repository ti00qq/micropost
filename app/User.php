<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 自分自身ではないかの確認
        $its_me = $this->id == $userId;

        if ($exist || $its_me) {
            // 既にフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }

    public function unfollow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 自分自身ではないかの確認
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            // 既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }

    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()-> pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
    
    //userがお気に入りに入れているmicropost群
    public function favorite_microposts()
    {
        return $this->belongsToMany(Micropost::class, 'user_favorite', 'user_id', 'favorite_id')->withTimestamps();
    }
    
    //micropostをお気に入りにいれているuser達
    public function begin_favorite_users()
    {
        return $this->belongsToMany(User::class, 'user_favorite', 'favorite_id', 'user_id')->withTimestamps();
    }
    
    //お気に入りに追加する
    public function favorite($micropostId)
    {
        // 既にお気に入りしているかの確認
        
        $exist = $this->is_favorite_microposts($micropostId);
        
        if ($exist==true) {
            // 既にお気に入りしていれば何もしない
            return false;
        } else {
            // お気に入りしていないのであれば追加する
            $this->favorite_microposts()->attach($micropostId);
            return true;
        }
    }
    
    //お気に入りから削除する
    public function unfavorite($micropostId)
    {
        // 既にお気に入りしているかの確認
        $exist = $this->is_favorite_microposts($micropostId);
        
        if ($exist==true) {
            // 既にお気に入りしていればお気に入りを外す
            $this->favorite_microposts()->detach($micropostId);
            return true;
        } else {
            // お気に入りしていないのであれば何もしない
            return false;
        }
    }
    
    //お気に入り追加状況
    public function is_favorite_microposts($micropostId)
    {
        return $this->favorite_microposts()->where('favorite_id', $micropostId)->exists();
    }
    
    //ログインユーザーがお気に入りに入れているmicropostの取得
    public function feed_favorite_microposts()
    {
        $favorite_user_ids = $this->favorite_microposts()-> pluck('user_favorite.favorite_id')->toArray();
        return Micropost::whereIn('id', $favorite_user_ids);
    }
}
