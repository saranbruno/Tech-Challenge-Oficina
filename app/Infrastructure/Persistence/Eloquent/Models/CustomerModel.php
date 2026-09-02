<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'document', 'document_type', 'email', 'phone'])]
class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'customers';
}
