<?php

namespace App\Modules\Image;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $casts = ['id' => 'integer', 'size' => 'integer', 'width' => 'integer', 'height' => 'integer', 'public' => 'integer'];

    protected $fillable = [
        'u_id', 'hash', 'name', 'size', 'width', 'height', 'type', 'alt', 'owner', 'public'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function getRouteKeyName()
    {
        return 'u_id';
    }

    public function scopeUID($query, $u_id)
    {
        $query->where('u_id', $u_id);
    }

    public function scopePublic($query, $public = 1)
    {
        $query->where('public', 1);
    }

    public function scopeCanAccess($query, $user_id)
    {
        $query->public()->orWhere('own', $user_id);
    }
}
