<?php

Route::get('images', 'ImageController@getImages');

//Route::get('storage/images/{image}', 'ImageController@getImageFile');
//Route::get('storage/images/thumbs/{image}', '\App\Modules\Image\Http\Controllers\ImageController@getThumbImageFile');

Route::post('image', 'ImageController@postImage');

Route::get('edit/{image}', 'ImageController@getImage');

Route::put('edit/{image}', 'ImageController@putImage');

Route::delete('image/{image}', 'ImageController@deleteImage');
