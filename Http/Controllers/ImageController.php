<?php

namespace App\Modules\Image\Http\Controllers;

use App\Modules\Image\ImageHelper;
use App\Http\Controllers\Controller;
use App\Modules\Image\Image;
use Illuminate\Support\Facades\Storage;
use ImageFactory;

/**
 * though we can use middleware instead of imageHelper's canEditImage method, we chose to stick with this implementation
 * since we care performance rather than better code,
 *
 * Class ImageController
 * @package App\Modules\Image\Http\Controllers
 */
class ImageController extends Controller
{
    private $imageHelper;

    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:ownership.image'], ['only' => [
            'getImages', 'getImage', 'putImage', 'postImage', 'deleteImage'
        ]]);

        $this->imageHelper = new ImageHelper();
    }

    public function getImages()
    {
        $images = Image::public()
            ->orWhere('owner', auth()->user()->user_id)
            ->get();

        return response()->json($images);
    }

    public function getImage(Image $image)
    {
        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id)) {
            abort(404);
        }

        return response()->json($image, 200);
    }

    public function postImage()
    {
        request()->validate([
            'file' => 'required|image|max:33554432',
            'public' => 'required',
            'name' => 'max:255',
        ]);

        $file = request()->file('file');
        $name = request()->input('name') ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->extension();
        $u_id = uniqid('img_', true) . '.' . $extension;
        $store_name = $u_id;
        $path = 'images/';

        $image = ImageFactory::make($file->getRealPath());
        $thumb_nail = ImageFactory::make($file->getRealPath());

        $this->imageHelper->resizeToThumbNail($thumb_nail, 384);

        $disk = request()->input('public') ? 'public' : 'local';

        Storage::disk($disk)->put($path . $store_name, $image->encode($extension, null));
        Storage::disk($disk)->put($path . 'thumbs/' . $store_name, $thumb_nail->encode($extension, null));

        $image = Image::create([
            'u_id' => $u_id,
            'size' => $file->getSize(),
            'name' => $name,
            'hash' => md5_file($file->getRealPath()),
            'height' => $image->height(),
            'width' => $image->width(),
            'type' => $extension,
            'alt' => request()->input('alt') ?? $name,
            'owner' => auth()->user()->user_id,
            'public' => request()->input('public')
        ]);

        return response()->json($image);
    }

    public function putImage(Image $image)
    {
        request()->validate([
            'name' => 'required|max:255',
            'alt' => 'required|max:255',
            'public' => 'required',
            'crop' => 'required'
        ]);

        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id)) {
            abort(404);
        }

        if (request()->input('crop') === 1) {
            $this->imageHelper->cropAndUpdateImage($image, [
                'width' => request()->input('width'),
                'height' => request()->input('height'),
                'x' => request()->input('x'),
                'y' => request()->input('y'),
                'rotate' => request()->input('rotate')
            ]);
        }

        $public = request()->input('public');

        if ($image->public !== $public) {
            $disk = $public ? 'public' : 'local';
            $current_disk = !$public ? 'public' : 'local';

            Storage::disk($disk)->writeStream("images/{$image->u_id}",
                Storage::disk($current_disk)->readStream("images/{$image->u_id}"));
            Storage::disk($disk)->writeStream("images/thumbs/{$image->u_id}",
                Storage::disk($current_disk)->readStream("images/thumbs/{$image->u_id}"));

            Storage::disk($current_disk)->delete("images/{$image->u_id}");
            Storage::disk($current_disk)->delete("images/thumbs/{$image->u_id}");
        }

        $image->name = request()->input('name');
        $image->alt = request()->input('alt');
        $image->public = $public;

        $image->save();

        return response()->json([
            'header' => 'İşlem Başarılı', 'message' => 'Fotoğraf kaydedildi', 'action' => 'Tamam', 'state' => 'success'
        ]);
    }

    public function deleteImage(Image $image)
    {
        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id)) {
            abort(404);
        }
        $disk = $image->public ? 'public' : 'local';

        Storage::disk($disk)->delete("images/$image->u_id");
        Storage::disk($disk)->delete("images/thumbs/$image->u_id");

        $image->forceDelete();

        return response()->json([
            'header' => 'İşlem Başarılı', 'message' => 'Dosyalar başarı ile silindi!', 'state' => 'success'
        ], 200);

    }

    public function getThumbImageFile(Image $image)
    {
        return $this->getFile($image, 'images/thumbs/');
    }

    public function getImageFile(Image $image)
    {
        return $this->getFile($image, 'images/');
    }

    private function getFile(Image $image, $path)
    {
        dump(auth()->user());
        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id ?? '')) {
            abort(404);
        }
        if (!Storage::disk('local')->has($path . $image->u_id)) {
            abort(404);
        }

        return response()->file($this->imageHelper->generateImageFilePath($image),
            ['Content-Type' => "image/$image->type"]
        );
    }
}
