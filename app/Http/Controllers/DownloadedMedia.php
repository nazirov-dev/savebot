<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downloaded_Media;

class DownloadedMedia extends Controller
{
    public function isMediaDownloaded(string $url, int $platform_id): bool | array
    {
        // platform ids [];
        return [];
    }
}
