<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'status',
        'chunk_count',
        'error_message',
    ];
}
