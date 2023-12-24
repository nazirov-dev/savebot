<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstagramDownloader extends Controller
{
    public function downloadPost($url): array | string
    {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://instagram-media-downloader.p.rapidapi.com/rapid/post_v2.php?url=" . urlencode($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: instagram-media-downloader.p.rapidapi.com",
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
    public function downloadStory($url): array | string
    {


        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://instagram-media-downloader.p.rapidapi.com/rapid/stories.php?url=" . urlencode($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: instagram-media-downloader.p.rapidapi.com",
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
    public function getMedia(string $url, string $type = 'post'): array
    {
        if ($type == 'post') {
            $Media = $this->downloadPost($url);
            if(is_array($Media)) {
                if(isset($Media['status']) && $Media['status'] == 'ok' && isset($Media['items'])) {
                    $item = $Media['items'][0];
                    if(isset($item['caption']) and !empty($item['caption']['text'])) {
                        $result = ['caption' => $item['caption']['text']];
                    } else {
                        $result = [];
                    }
                    if($item['media_type'] == 8 && $item['product_type'] == 'carousel_container') {
                        $result['ok'] = true;
                        $result['medias'] = [];
                        $result['medias_count'] = 0;
                        foreach ($item['carousel_media'] as $media) {
                            $type = $media['media_type'] == 1 ? 'photo' : 'video';
                            if($media['media_type'] == 1) {
                                $type = 'photo';
                                $url = $media['image_versions2']['candidates'][0]['url'];
                            } elseif($media['media_type'] == 2) {
                                $type = 'video';
                                $url = $media['video_versions'][0]['url'];
                            } else {
                                continue;
                            }
                            $result['medias'][] = ['type' => $type, 'url' => $url];
                            $result['medias_count']++;
                        }
                        return $result;
                    } elseif($item['media_type'] == 1) {
                        $result['ok'] = true;
                        $result['medias'][] = ['type' => 'photo', 'url' => $item['image_versions2']['candidates'][0]['url']];
                        $result['medias_count'] = 1;
                        return $result;
                    } elseif($item['media_type'] == 2) {
                        $result['ok'] = true;
                        $result['medias'][] = ['type' => 'video', 'url' => $item['video_versions'][0]['url']];
                        $result['medias_count'] = 1;
                        return $result;
                    }
                } else {
                    Log::error("Instagram downloader wrong URL: $url\n", $Media);
                    return ['ok' => false, 'error_message' => 'Wrong URL'];
                }
            } else {
                Log::error("Instagram downloader curl error: \n" . $Media);
                return ['ok' => false, 'error_message' => $Media];
            }
        } elseif($type == 'story') {
            $Story = $this->downloadStory($url);
            if(is_array($Story)) {
                if(empty($Story)) {
                    Log::error("Instagram downloader story wrong URL: $url\n" . $Story);
                    return ['ok' => false, 'error_message' => 'Wrong URL'];
                } elseif(isset($Story['video'])) {
                    return ['ok' => true, 'medias' => ['type' => 'video', 'url' => $Story['video']], 'medias_count' => 1];
                } elseif(isset($Story['image']) && !isset($Story['video'])) {
                    return ['ok' => true, 'medias' => ['type' => 'photo', 'url' => $Story['image']], 'medias_count' => 1];
                } else {
                    Log::error("Instagram downloader story downloading undefined error: $url\n" . $Story);
                    return ['ok' => false, 'error_message' => 'Wrong URL'];
                }
            }
        }
        return [];
    }
}
