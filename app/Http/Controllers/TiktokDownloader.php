<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TiktokDownloader extends Controller
{
    public function download(string $url): string | array
    {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://all-media-downloader-v2.p.rapidapi.com/dl",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'url' => $url
            ]),
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: all-media-downloader-v2.p.rapidapi.com",
                "X-RapidAPI-Key: dd803fa80bmsh844c6af94a7cf81p174525jsnad0e84670fce",
                "content-type: application/json"
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
        $Media = $this->download($url);
        if(is_array($Media)) {
            $format = end($Media['formats']);
            if(!empty($format)) {
                $result = ['ok' => true, 'medias_count' => 1];
                if(!empty($Media['title'])) {
                    $result['caption'] = $Media['title'];
                }
                $result['medias'] = [['type' => 'video', 'url' => $format['url']]];
                return $result;
            } else {
                Log::error('TikTok Downloader video format not found: ', $Media);
                return ['ok' => false,'error_message' => 'Yuklab bo\'lmadi'];
            }
        } else {
            Log::error('TikTok Downloader curl error: ' . $Media);
            return ['ok' => false,'error_message' => $Media];
        }
    }
}
