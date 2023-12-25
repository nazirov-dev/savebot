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
            ->select('type', DB::raw('media_id as url'), DB::raw('description as caption'))
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
    public static function create(array $message, string $url, string|null $caption, int $user_id, int $platform_id): void
    {
        if(array_key_exists('message_id', $message['result'])) { // one media
            $info = self::getMessageTypeAndMediaFileId($message);
            Downloaded_Media::create([
                'url' => $url,
                'media_id' => $info['media_id'],
                'user_id' => $user_id,
                'platform_id' => $platform_id,
                'media_group_id' => $info['media_group_id'],
                'type' => $info['type'],
                'description' => $caption
            ]);
        } else { //multi media
            foreach ($message['result'] as $msg) {
                $info = self::getMessageTypeAndMediaFileId($msg);
                Downloaded_Media::create([
                    'url' => $url,
                    'media_id' => $info['media_id'],
                    'user_id' => $user_id,
                    'platform_id' => $platform_id,
                    'media_group_id' => $info['media_group_id'],
                    'type' => $info['type'],
                    'description' => $caption
                ]);
            }
        }
    }
    public static function getMessageTypeAndMediaFileId(array $message)
    {
        if(array_key_exists('video', $message)) {
            return ['type' => 'video', 'media_id' => $message['video']['file_id'], 'media_group_id' => !empty($message['media_group_id']) ? $message['media_group_id'] : null];
        } elseif(array_key_exists('photo', $message)) {
            return ['type' => 'photo', 'media_id' => end($message['photo'])['file_id'], 'media_group_id' => !empty($message['media_group_id']) ? $message['media_group_id'] : null];
        }
    }
}
