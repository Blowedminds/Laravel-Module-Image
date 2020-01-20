<?php


namespace App\Modules\Image;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ImageFactory;

class ImageableHelper
{
    public function deleteImages(array $images)
    {
        Imageable::whereIn('image', $images)->delete();

        foreach ($images as $image) {
            $this->deleteImageFile($image);
        }
    }

    public function updateWeights(array $imageIdsAndWeights)
    {
        $table = Imageable::getModel()->getTable();

        $cases = [];
        $ids = [];
        $params = [];

        foreach ($imageIdsAndWeights as $value) {
            if (!array_key_exists('id', $value) || !array_key_exists('weight', $value)) {
                abort(422);
            }

            $id = (int)$value['id'];
            $cases[] = "WHEN {$id} then ?";
            $params[] = $value['weight'];
            $ids[] = $id;
        }

        $ids = implode(',', $ids);

        $cases = implode(' ', $cases);

        $params[] = Carbon::now();

        DB::update("UPDATE `{$table}` SET `weight` = CASE `id` {$cases} END, `updated_at` = ? WHERE `id` in ({$ids})", $params);

        return response()->json();
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
