<?php

Route::get('images', 'ImageController@getImages');

//Route::get('storage/images/{image}', 'ImageController@getImageFile');
//Route::get('storage/images/thumbs/{image}', '\App\Modules\Image\Http\Controllers\ImageController@getThumbImageFile');

Route::post('image', 'ImageController@putImage');

Route::get('edit/{image}', 'ImageController@getImage');

Route::post('edit/{image}', 'ImageController@postImage');

Route::delete('image/{image}', 'ImageController@deleteImage');

Route::get('thumb/{image}', 'ImageController@getThumbImageFile');
