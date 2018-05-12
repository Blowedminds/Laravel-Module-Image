<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */

  protected $casts = [ 'id' => 'integer', 'size' => 'integer', 'width' => 'integer', 'height' => 'integer', 'public' => 'integer' ];

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

}
