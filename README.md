# Laravel-Module-Image

This module supports backend for Angular-Module-Image

**Required packages**
1. "intervention/image": "^2.4"

**Required Modules**
*--no required modules--*

**Functionalities**
1. Add, Edit (crop) , Delete image

**Installation**
1. Add the module to Laravel project as a submodule. 
`git submodule add https://github.com/bwqr/Laravel-Module-Image app/Modules/Image`
2. Add the route file `Http/image.php` to `app/Providers/RouteServiceProvider.php`
 and register inside the `map` function, eg.  
 `
    protected function mapImageRoutes()
    {
        Route::prefix('image')
            ->middleware('api')
            ->namespace($this->moduleNamespace . "\Image\Http\Controllers")
            ->group(base_path('app/Modules/Image/Http/image.php'));
    }
 `
3. Migrate the database. `php artisan migrate --path=/app/Modules/Image/Database/migrations`

4. Add the following routes to `routes/web.php` , 
`
    Route::get('storage/images/{image}', 'ImageController@getImageFile');
    Route::get('storage/images/thumbs/{image}', '\App\Modules\Image\Http\Controllers\ImageController@getThumbImageFile');
`    

