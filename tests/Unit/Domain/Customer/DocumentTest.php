<?php

namespace Tests\Unit\Domain\Customer;

use App\Domain\Customer\Enums\DocumentType;
use App\Domain\Customer\Exceptions\InvalidDocument;
use App\Domain\Customer\ValueObjects\Document;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    #[DataProvider('validDocuments')]
    public function test_it_normalizes_and_validates_documents(string $input, string $normalized, DocumentType $type): void
    {
        $document = new Document($input);

        $this->assertSame($normalized, $document->value);
        $this->assertSame($type, $document->type);
    }

    #[DataProvider('invalidDocuments')]
    public function test_it_rejects_invalid_documents(string $input): void
    {
        $this->expectException(InvalidDocument::class);

        new Document($input);
    }

    public static function validDocuments(): array
    {
        return [
            ['529.982.247-25', '52998224725', DocumentType::Cpf],
            ['04.252.011/0001-10', '04252011000110', DocumentType::Cnpj],
        ];
    }

    public static function invalidDocuments(): array
    {
        return [
            ['123'],
            ['111.111.111-11'],
            ['529.982.247-24'],
            ['00.000.000/0000-00'],
            ['04.252.011/0001-11'],
        ];
    }
}
