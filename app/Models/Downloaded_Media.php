<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Downloaded_Media extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'downloaded_media';
    protected $fillable = [
        'url', 'media_id', 'user_id', 'platform_id', 'media_group_id', 'type', 'description', 'created_at', 'updated_at'
    ];
}
