<?php

namespace App\Modules\Image\Tests\Unit;

use App\Modules\Core\User;
use \Tests\TestCase as BaseTestCase;

class ImageControllerTest extends BaseTestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->make();
    }

    public function testInterventionLibrary()
    {
        $this->assertTrue(class_exists('ImageFactory'));
    }
}
