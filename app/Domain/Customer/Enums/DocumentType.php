<?php

namespace App\Domain\Customer\Enums;

enum DocumentType: string
{
    case Cpf = 'cpf';
    case Cnpj = 'cnpj';
}
