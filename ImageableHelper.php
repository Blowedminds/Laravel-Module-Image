<?php


namespace App\Modules\Image;


use App\Modules\Core\Traits\Weightable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ImageFactory;

class ImageableHelper
{
    use Weightable;

    public function deleteImages(array $images)
    {
        Imageable::whereIn('image', $images)->delete();

        foreach ($images as $image) {
            $this->deleteImageFile($image);
        }
    }

    public function updateImagesWeights(array $imageIdsAndWeights)
    {
        $this->updateWeights(Imageable::getModel()->getTable(), $imageIdsAndWeights);
    }

    public function storeImageFile($imageFile)
    {
        $storeName = uniqid('', false) . '.' . $imageFile->extension();

        $path = Storage::disk('public')->path('nanny/images/');

        $thumbNail = ImageFactory::make($imageFile->getRealPath());

        $thumbNail->resize(256, null, function ($c) {
            $c->aspectRatio();
        });

        Storage::disk('public')->put('nanny/thumbs/' . $storeName, $thumbNail->encode($imageFile->getExtension(), null));

        $imageFile->move($path, $storeName);

        return $storeName;
    }

    private function deleteImageFile($image)
    {
        Storage::disk('public')->delete('nanny/images/' . $image);
    }
}
