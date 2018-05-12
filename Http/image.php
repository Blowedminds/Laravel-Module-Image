<?php

Route::get('images', 'ImageController@getImages');

Route::get('image/{image}', 'ImageController@getImage');

Route::post('image', 'ImageController@postImage');

Route::get('edit/{image}', 'ImageController@getEdit');

Route::put('edit/{image}', 'ImageController@putEdit');

Route::delete('image/{image}', 'ImageController@deleteImage');

Route::get('thumb/{image}', 'ImageController@getThumb');
