<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LikeeDownloader extends Controller
{
    public function download(string $url): string | array | null
    {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://likee-downloader-download-likee-videos.p.rapidapi.com/process?url=" . urlencode($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: likee-downloader-download-likee-videos.p.rapidapi.com",
                "X-RapidAPI-Key: dd803fa80bmsh844c6af94a7cf81p174525jsnad0e84670fce"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return  "cURL Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }
    public function getMedia(string $url): array
    {
        $Media = $this->download($url);
        if(is_array($Media)) {
            if(!empty($Media['withoutWater'])) {
                return ['ok' => true, 'medias' => [['type' => 'video','url' => $Media['withoutWater']]], 'medias_count' => 1, 'caption' => (!empty($Media['msg_text']) && $Media['msg_text'] != 'Likee-Global video creation and sharing platform') ? $Media['msg_text'] : null];
            } elseif(!empty($Media['withWater'])) {
                return ['ok' => true, 'medias' => [['type' => 'video','url' => $Media['withWater']]], 'medias_count' => 1, 'caption' => (!empty($Media['msg_text']) && $Media['msg_text'] != 'Likee-Global video creation and sharing platform') ? $Media['msg_text'] : null];
            } else {
                Log::error('Likee Downloader video format not found: ', $Media);
                return ['ok' => false,'error_message' => 'Yuklab bo\'lmadi'];
            }
        } else {
            Log::error('Likee Downloader curl error: ' . $Media);
            return ['ok' => false,'error_message' => $Media];
        }
    }
}
