<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookDownloader extends Controller
{
    public function donwload(string $url): array | string
    {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://facebook-reel-and-video-downloader.p.rapidapi.com/app/main.php?url=" . urlencode($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: facebook-reel-and-video-downloader.p.rapidapi.com",
                "X-RapidAPI-Key: dd803fa80bmsh844c6af94a7cf81p174525jsnad0e84670fce"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }
    public function getMedia(string $url): array
    {
        $Media = $this->donwload($url);
        if(is_array($Media)) {
            if($Media['success']) {
                $result = ['ok' => true,'medias' => [], 'medias_count' => 0];
                if(!empty($Media['title']) && $Media['title'] != 'Facebook') {
                    $result['caption'] = $Media['title'];
                }
                foreach($Media['media'] as $media) {
                    if($media['type'] == 'Photo') {
                        $result['medias'][] = ['type' => 'photo', 'url' => $media['image']];
                        $result['medias_count']++;
                    } elseif($media['type'] == 'Video') {
                        if(!empty($media['hd_url'])) {
                            $url = $media['hd_url'];
                        } elseif(!empty($media['sd_url'])) {
                            $url = $media['sd_url'];
                        } else {
                            continue;
                        }
                        $result['medias'][] = ['type' => 'video', 'url' => $url];
                        $result['medias_count']++;
                    } else {
                        continue;
                    }
                }
                return $result;
            } else {
                Log::error("Facebook downloader wrong URL $url: \n", $Media);
                return ['ok' => false, 'error_message' => 'Wrong URL'];
            }
        } else {
            Log::error("Facebook downloader curl error: \n" . $Media);
            return ['ok' => false, 'error_message' => $Media];
        }
    }
}
