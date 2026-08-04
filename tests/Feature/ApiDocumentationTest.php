<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_swagger_ui_is_reachable(): void
    {
        $this->get('/api/documentation')->assertOk();
    }

    public function test_openapi_json_is_reachable(): void
    {
        $response = $this->get('/docs');

        $response->assertOk();
        $response->assertJsonPath('info.title', 'Demera API');
    }
}
