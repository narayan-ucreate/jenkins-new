<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PDO;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        \Artisan::call('migrate', ['--force' => 'true' ]).
        \Artisan::call('db:seed', ['--force' => 'true']);
        $this->assertTrue(true);
    }
}
