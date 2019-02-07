<?php


namespace App\Modules\Image;


use Illuminate\Support\Facades\Storage;

use ImageFactory;

class ImageHelper
{
    public function resizeToThumbNail($imageF, $dim)
    {
        $width = $imageF->width();
        $height = $imageF->height();

        if ($width >= $dim || $height >= $dim) {

            if ($width > $height) {
                $imageF->resize(128, null, function ($c) {
                    $c->aspectRatio();
                });
            } else {
                $imageF->resize(null, 128, function ($c) {
                    $c->aspectRatio();
                });
            }
        }
    }

    public function canEditImage($image, $user_id)
    {
        return $image->public === 1 || $image->owner === $user_id;
    }

    public function cropAndUpdateImage($image, $cropData)
    {
        $cropImageF = ImageFactory::make($this->generateImageFilePath($image));

        $cropImageF->rotate(360 - $cropData['rotate']);
        $cropImageF->crop($cropData['width'], $cropData['height'], $cropData['x'], $cropData['y']);

        $image->width = $cropImageF->width();
        $image->height = $cropImageF->height();

        $cropImageF->save();

        $image->size = Storage::size($this->generateRelativePath($image));
        $image->hash = md5_file($this->generateImageFilePath($image));

        $thumbNailF = ImageFactory::make($cropImageF);

        $this->resizeToThumbNail($thumbNailF, 128);

        $thumbNailF->save($this->generateThumbImageFilePath($image));
    }

    public function generateThumbImageFilePath($image)
    {
        return $this->generateImageFilePath($image, 'thumbs/');
    }

    public function generateImageFilePath($image, $prefix = '')
    {
        return storage_path('/app/'.$this->generateRelativePath($image, $prefix));
    }

    public function generateRelativePath($image, $prefix = '')
    {
        $public = $image->public ? 'public/' : '';

        return $public . "images/$prefix$image->u_id";
    }
}
