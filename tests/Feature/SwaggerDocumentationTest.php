<?php

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class SwaggerDocumentationTest extends TestCase
{
    public function test_swagger_ui_is_available(): void
    {
        $this->get('/docs')
            ->assertOk()
            ->assertSee('Tech-Challenge-Oficina API')
            ->assertSee('swagger-ui-dist@5.32.1', false)
            ->assertSee("url: '/docs/openapi.yaml'", false);
    }

    public function test_openapi_document_is_available_to_swagger_ui(): void
    {
        $response = $this->get('/docs/openapi.yaml')
            ->assertOk()
            ->assertHeader('content-type', 'application/yaml');

        self::assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        self::assertSame(
            file_get_contents(base_path('docs/openapi.yaml')),
            $response->baseResponse->getFile()->getContent(),
        );
    }
}
