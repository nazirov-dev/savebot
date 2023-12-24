<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downloaded_Media;
use Illuminate\Support\Facades\DB;

class DownloadedMedia extends Controller
{
    public static function getMediaOrFalse(string $url, int $platform_id): bool | array
    {
        // platforms
        // $platforms = array(
        //     array('id' => '1','name' => 'Instagram'),
        //     array('id' => '2','name' => 'Facebook'),
        //     array('id' => '3','name' => 'TikTok'),
        //     array('id' => '4','name' => 'Likee'),
        //     array('id' => '5','name' => 'YouTube'),
        //     array('id' => '6','name' => 'Pinterest')
        //   );
        // Query to check if the media exists
        $media = Downloaded_Media::where(['platform_id' => $platform_id, 'url' => $url])
            ->select('type', DB::raw('media_id as url'))
            ->get();


        // Check if media exists
        if ($media->isNotEmpty()) {
            // If media exists, return its data
            return $media->toArray();
        } else {
            // If media does not exist, return false
            return false;
        }
    }
    public static function create(array $message, string $url, string|null $caption, int $user_id, int $platform_id) {}
}
