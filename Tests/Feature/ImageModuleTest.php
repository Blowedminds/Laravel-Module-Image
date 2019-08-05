<?php

namespace App\Modules\Image\Tests\Feature;

use App\Modules\Core\User;
use App\Modules\Image\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Modules\Core\Tests\TestCase;
use Illuminate\Support\Str;

class ImageModuleTest extends TestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([\App\Modules\Core\Http\Middleware\Permission::class]);

        $this->user = factory(User::class)->make();
    }

    public function testRoutes(): void
    {
        $this->assertTrue($this->checkRoute($this->imageRoute . 'images'));
        $this->assertTrue($this->checkRoute($this->imageRoute . 'image', 'post'));
        $this->assertTrue($this->checkRoute($this->imageRoute . 'edit/{image}'));
        $this->assertTrue($this->checkRoute($this->imageRoute . 'edit/{image}', 'put'));
        $this->assertTrue($this->checkRoute($this->imageRoute . 'image/{image}', 'delete'));
        $this->assertTrue($this->checkRoute('storage/images/{image}'));
        $this->assertTrue($this->checkRoute('storage/images/thumbs/{image}'));
    }

    public function testGetImages(): void
    {
        $this->getManyTest(Image::class, $this->imageRoute . 'images', $this->user);
    }

    public function testImage(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $file = UploadedFile::fake()->image('image.jpg');

        $inputs = [
            ['public' => false, 'name' => 'Test File Name', 'file' => $file],
            ['public' => true, 'name' => 'Test File Name', 'file' => $file],
            ['public' => true, 'file' => $file],
            ['public' => false, 'file' => $file],
        ];

        foreach ($inputs as $input) {
            //Post
            $post = $this->actingAs($this->user)->json('POST', $this->imageRoute . 'image', $input);

            $post->assertStatus(200);

            $image = json_decode($post->getContent(), true);

            $disk = $input['public'] ? 'public' : 'local';

            Storage::disk($disk)->assertExists("images/{$image['u_id']}");
            Storage::disk($disk)->assertExists("images/thumbs/{$image['u_id']}");

            //Get
            $this->actingAs($this->user)->get($this->imageRoute . "edit/{$image['u_id']}")->assertStatus(200);

            //Put
            $this->actingAs($this->user)->json('PUT', $this->imageRoute . "edit/{$image['u_id']}", [
                'public' => !$image['public'],
                'crop' => 0,
                'name' => $image['name'] . Str::random(10),
                'alt' => $image['alt'] . Str::random(2)
            ])->assertStatus(200);

            $this->actingAs($this->user)->json('PUT', $this->imageRoute . "edit/{$image['u_id']}", [
                'public' => $image['public'],
                'crop' => 1,
                'width' => random_int(1, 1000), 'height' => random_int(1, 1000),
                'name' => $image['name'] . Str::random(10),
                'alt' => $image['alt'] . Str::random(2)
            ])->assertStatus(200);

            //Delete
            $this->actingAs($this->user)->delete($this->imageRoute . "image/{$image['u_id']}")->assertStatus(200);
        }
    }
}
