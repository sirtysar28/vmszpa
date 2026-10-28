<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_ke_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
