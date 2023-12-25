<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Medium;

class PinterestDownloader extends Controller
{
    public function download(string $url): string | array | null
    {
        $short_code = str_replace('https://pin.it/', '', $url);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://pinterest-scraper.p.rapidapi.com/pint/?shortcode=" . $short_code,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: pinterest-scraper.p.rapidapi.com",
                "X-RapidAPI-Key: dd803fa80bmsh844c6af94a7cf81p174525jsnad0e84670fce"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_encode($response, true);
        }
    }

    public function getMedia(string $url): array
    {
        $Media = $this->download($url);
        if(is_array($Media)) {
            if($Media['status'] == 'success' && $Media['message'] == 'ok') {
                $data = $Media['data'];
                if(!empty($data['closeup_unified_description']) or !empty($data['title'])) {
                    $caption = $data['title'] . "\n";
                    $caption .= $data['closeup_unified_description'];
                } else {
                    $caption = null;
                }
                if(!empty($data['videos']) && !empty($data['videos']['video_list']) && !empty($data['videos']['video_list']['V_720P']['url'])) {
                    return ['ok' => true, 'medias' => [['type' => 'video', 'url' => $data['videos']['video_list']['V_720P']['url']]], 'medias_count' => 1, 'caption' => $caption];
                } elseif(empty($data['videos']) && !empty($data['images'])) {
                    return ['ok' => true, 'medias' => [['type' => 'photo', 'url' => end($data['images'])['url']]], 'medias_count' => 1, 'caption' => $caption];
                } else {
                    Log::error("Pinterest downloader wrong URL: $url\n", $Media);
                    return ['ok' => false, 'error_message' => 'Wrong URL'];
                }
            } else {
                Log::error("Pinterest downloader wrong URL: $url\n", $Media);
                return ['ok' => false, 'error_message' => 'Wrong URL'];
            }
        } else {
            Log::error('Pinterest Downloader curl error: ' . $Media);
            return ['ok' => false, 'error_message' => $Media];
        }
    }
}
