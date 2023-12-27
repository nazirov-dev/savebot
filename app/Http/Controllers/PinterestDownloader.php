<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Downloaded_Media;

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
            return json_decode($response, true);
        }
    }
    public function download1(string $url): string | array | null
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://pinterest-video-and-image-downloader.p.rapidapi.com/pinterest?url=" . urlencode($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: pinterest-video-and-image-downloader.p.rapidapi.com",
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
    public function download2(string $url): string | array | null
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://auto-download-all-in-one.p.rapidapi.com/v1/social/autolink",
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
                "X-RapidAPI-Host: auto-download-all-in-one.p.rapidapi.com",
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
        $count_of_pinterest_media = Downloaded_Media::where('platform_id', 6)->count();
        if($count_of_pinterest_media <= 35) {
            if(strpos($url, 'pinterest.com') !== false) {
                return ['ok' => false];
            }
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
        } elseif ($count_of_pinterest_media <= 125) {
            $Media = $this->download1($url);
            if(is_array($Media)) {
                if($Media['success'] && !empty($Media['data']) && !empty($Media['type'])) {
                    $data = $Media['data'];
                    if(!empty($data['title'])) {
                        $caption = $data['title'];
                    } else {
                        $caption = null;
                    }
                    if($Media['type'] == 'image') {
                        if(empty($data['carousel'])) {
                            return ['ok' => true, 'medias' => [['type' => 'photo', 'url' => $data['url']]], 'medias_count' => 1, 'caption' => $caption];
                        } else {
                            $images = [];
                            $i = 0;
                            foreach($data['carousel'] as $image) {
                                $images[] = ['type' => 'photo', 'url' => $image['url']];
                                $i++;
                            }
                            return ['ok' => true, 'medias' => $images, 'medias_count' => $i, 'caption' => $caption];
                        }
                    } elseif($Media['type'] == 'video') {
                        return ['ok' => true, 'medias' => [['type' => 'video', 'url' => $data['url']]], 'medias_count' => 1, 'caption' => $caption];
                    } else {
                        Log::error("Pinterest downloader wrong URL (api2): $url\n", $Media);
                        return ['ok' => false];
                    }
                } else {
                    Log::error("Pinterest downloader wrong URL (api2): $url\n", $Media);
                    return ['ok' => false, 'error_message' => 'Wrong URL'];
                }
            } else {
                Log::error('Pinterest Downloader curl error (api2): ' . $Media);
                return ['ok' => false, 'error_message' => $Media];
            }
        } elseif ($count_of_pinterest_media <= 215) {
            $Media = $this->download2($url);
            if(is_array($Media)) {
                if(!$Media['error'] && !empty($Media['medias'])) {
                    $data = $Media['medias'][0];

                    if($data['type'] == 'image') {
                        return ['ok' => true, 'medias' => [['type' => 'photo', 'url' => $data['url']]], 'medias_count' => 1, 'caption' => null];
                    } elseif($data['type'] == 'video') {
                        return ['ok' => true, 'medias' => [['type' => 'video', 'url' => $data['url']]], 'medias_count' => 1, 'caption' => null];
                    } else {
                        Log::error("Pinterest downloader wrong URL (api3): $url\n", $Media);
                        return ['ok' => false];
                    }

                } else {
                    Log::error("Pinterest downloader wrong URL (api3): $url\n", $Media);
                    return ['ok' => false, 'error_message' => 'Wrong URL'];
                }
            } else {
                Log::error('Pinterest Downloader curl error (api3): ' . $Media);
                return ['ok' => false, 'error_message' => $Media];
            }
        } else {
            return ['ok' => false];
        }
    }
}
