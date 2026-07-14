<?php

namespace Tests\Unit\Interfaces\Http\Responses;

use App\Interfaces\Http\Responses\ApiErrorResponse;
use Tests\TestCase;

class ApiErrorResponseTest extends TestCase
{
    public function test_it_builds_the_standard_error_envelope(): void
    {
        $response = ApiErrorResponse::make(
            'validation_error',
            'Dados invalidos.',
            422,
            ['name' => ['O nome e obrigatorio.']],
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'error' => [
                'code' => 'validation_error',
                'message' => 'Dados invalidos.',
                'details' => ['name' => ['O nome e obrigatorio.']],
            ],
        ], $response->getData(true));
    }

    public function test_it_omits_empty_details(): void
    {
        $response = ApiErrorResponse::make('not_found', 'Not Found', 404);

        $this->assertSame([
            'error' => [
                'code' => 'not_found',
                'message' => 'Not Found',
            ],
        ], $response->getData(true));
    }
}
