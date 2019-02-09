<?php

namespace App\Modules\Image\Http\Controllers;

use App\Modules\Image\ImageHelper;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;

use App\Modules\Image\Image;
use ImageFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $this->middleware('auth:api', ['only' => [
            'getImages', 'getImage', 'putImage', 'postImage', 'deleteImage'
        ]]);

        $this->imageHelper = new ImageHelper();
    }

    public function getImages()
    {
        $images = Image::public()
            ->orWhere('owner', auth()->user()->user_id)
            ->get()
            ->reduce(function ($carry, $image) {

                $key = $image->public == 0 ? 'private' : 'public';

                $carry[$key][] = $image;

                return $carry;
            }, ['private' => [], 'public' => []]);

        return response()->json($images, 200);
    }

    public function getImage(Image $image)
    {
        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id)) {
            throw new NotFoundHttpException();
        }

        return response()->json($image, 200);
    }

    /**
     * This method should be called by PUT method, however with PUT method we cannot upload files, at least I was not able to do
     * so we used POST method and keep naming putImage
     * @return \Illuminate\Http\JsonResponse
     */
    public function putImage()
    {
        request()->validate([
            'file' => 'required|image|max:33554432',
            'public' => 'required',
            'name' => 'max:255',
        ]);

        $file = request()->file('file');
        $name = request()->input('name') ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->extension();
        $u_id = uniqid('img_');
        $store_name = "$u_id.$extension";
        $path = "albums/$u_id/";

        $image = ImageFactory::make($file->getRealPath());
        $thumb_nail = ImageFactory::make($file->getRealPath());

        $this->imageHelper->resizeToThumbNail($thumb_nail, 128);

        Storage::put($path . $store_name, $image->encode($extension, null));
        Storage::put($path . 'thumb_' . $store_name, $thumb_nail->encode($extension, null));

        Image::create([
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

        return response()->json([
            'header' => 'İşlem Başarılı',
            'message' => 'Fotoğrafı albüme kaydettik',
            'action' => 'Tamam',
            'state' => 'success',
            'data' => ['u_id' => $u_id]
        ], 200);
    }

    public function postImage(Image $image)
    {
        request()->validate([
            'name' => 'required|max:255',
            'alt' => 'required|max:255',
            'public' => 'required',
            'crop' => 'required'
        ]);

        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id)) {
            throw new NotFoundHttpException();
        }

        if (request()->input('crop') == 1) {
            $this->imageHelper->cropAndUpdateImage($image, [
                'width' => request()->input('width'),
                'height' => request()->input('height'),
                'x' => request()->input('x'),
                'y' => request()->input('y'),
                'rotate' => request()->input('rotate')
            ]);
        }

        $image->name = request()->input('name');
        $image->alt = request()->input('alt');
        $image->public = request()->input('public');

        $image->save();

        return response()->json([
            'header' => 'İşlem Başarılı', 'message' => 'Fotoğraf kaydedildi', 'action' => 'Tamam', 'state' => 'success'
        ]);
    }

    public function deleteImage(Image $image)
    {
        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id)) {
            throw new NotFoundHttpException();
        }

        Storage::deleteDirectory("albums/$image->u_id");

        $image->forceDelete();

        return response()->json([
            'header' => 'İşlem Başarılı', 'message' => 'Dosyalar başarı ile silindi!', 'state' => 'success'
        ], 200);

    }

    public function getThumbImageFile(Image $image)
    {
        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id ?? '')) {
            throw new NotFoundHttpException();
        }

        return response()->file($this->imageHelper->generateThumbImageFilePath($image),
            ['Content-Type' => "image/$image->type"]
        );
    }

    public function getImageFile(Image $image)
    {
        if (!$this->imageHelper->canEditImage($image, auth()->user()->user_id ?? '')) {
            throw new NotFoundHttpException();
        }

        return response()->file($this->imageHelper->generateImageFilePath($image),
            ['Content-Type' => "image/$image->type"]
        );
    }
}
