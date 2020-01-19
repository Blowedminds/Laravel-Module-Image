<?php

namespace App\Modules\Image;

use Illuminate\Database\Eloquent\Model;

class Imageable extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'image', 'imageable_type', 'imageable_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function imageable()
    {
        return $this->morphTo();
    }
}
