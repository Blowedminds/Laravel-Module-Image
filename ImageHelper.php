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
                $imageF->resize($dim, null, function ($c) {
                    $c->aspectRatio();
                });
            } else {
                $imageF->resize(null, $dim, function ($c) {
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
        $image_path = $this->generateImageFilePath($image);
        $cropImageF = ImageFactory::make($image_path);

        $cropImageF->rotate(360 - $cropData['rotate']);
        $cropImageF->crop($cropData['width'], $cropData['height'], $cropData['x'], $cropData['y']);

        $image->width = $cropImageF->width();
        $image->height = $cropImageF->height();

        $cropImageF->save();

        $image->size = Storage::disk($image->public ? 'public' : 'local')->size($this->generateRelativePath($image));
        $image->hash = md5_file($image_path);

        $thumbNailF = ImageFactory::make($cropImageF);

        $this->resizeToThumbNail($thumbNailF, 256);

        $thumbNailF->save($this->generateThumbImageFilePath($image));
    }

    public function generateThumbImageFilePath($image)
    {
        return $this->generateImageFilePath($image, 'thumbs/');
    }

    public function generateImageFilePath($image, $prefix = '')
    {
        $disk = $image->public ? 'public' : 'local';

        return Storage::disk($disk)->path($this->generateRelativePath($image, $prefix));
    }

    public function generateRelativePath($image, $prefix = '')
    {
        return "images/$prefix$image->u_id";
    }
}
