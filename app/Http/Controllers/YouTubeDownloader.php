<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YouTubeDownloader extends Controller
{
    public function download(string $url): string | array | null
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
            $formats = [];
            foreach ($Media['formats']  as $format) {
                if($format['format_id'] == '18' or $format['format_id'] == '22') {
                    $formats[$format['format_id']] = $format;
                }
            }
            if(!empty($formats)) {
                function filesize_formatted($size)
                {
                    $power = $size > 0 ? floor(log($size, 1024)) : 0;
                    return number_format($size / pow(1024, $power), 2);
                }
                $result = ['ok' => true, 'medias_count' => 1];
                if(!empty($Media['title'])) {
                    $result['caption'] = $Media['title'];
                }
                if(isset($formats['22'])) {
                    $size = filesize_formatted($formats['22']['filesize']);
                    if($size > 49.5) {
                        $result['ok'] = false;
                    } else {
                        $result['medias'] = [['type' => 'video', 'url' => $formats['22']['url']]];
                    }
                } elseif(isset($formats['18'])) {
                    $size = filesize_formatted($formats['18']['filesize']);
                    if($size > 49.5) {
                        $result['ok'] = false;
                    } else {
                        $result['medias'] = [['type' => 'video', 'url' => $formats['18']['url']]];
                    }
                } else {
                    $result['ok'] = false;
                    $result['error_message'] = 'Yuklab bo\'lmadi';
                }
                return $result;
            } else {
                Log::error('Youtube Downloader video format not found: ', $Media);
                return ['ok' => false,'error_message' => 'Yuklab bo\'lmadi'];
            }
        } else {
            Log::error('Youtube Downloader curl error: ' . $Media);
            return ['ok' => false,'error_message' => $Media];
        }
    }
}
