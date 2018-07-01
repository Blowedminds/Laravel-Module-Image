<?php

Route::get('images', 'ImageController@getImages');

Route::get('image/{image}', 'ImageController@getImageFile');

Route::post('image', 'ImageController@putImage');

Route::get('edit/{image}', 'ImageController@getImage');

Route::post('edit/{image}', 'ImageController@postImage');

Route::delete('image/{image}', 'ImageController@deleteImage');

Route::get('thumb/{image}', 'ImageController@getThumbImageFile');
