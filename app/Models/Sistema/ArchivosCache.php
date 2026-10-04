<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivosCache extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "archivos_caches";

    protected $fillable = [
        'tipo_archivo',
        'relative_path',
        'name_file',
        'url_archivo',
        'created_by',
        'updated_by'
    ];
    
}
