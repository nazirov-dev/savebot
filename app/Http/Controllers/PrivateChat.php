<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TempFileController;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BotUser;
use App\Models\Text;
use App\Models\Lang;
use App\Models\Channel;
use Illuminate\Support\Facades\Storage;

class PrivateChat extends Controller
{
    public function __construct() {}
    public function check_user_subscribed_to_channels($bot, $user_id)
    {
        $not_subscribed_channels = [];
        $channels = Channel::where(['status' => 1])->get()->toArray();
        foreach ($channels as $channel) {
            $status = $bot->getChatMember([
                'chat_id' => $channel['channel_id'],
                'user_id' => $user_id
            ])['result']['status'];
            if (!in_array($status, ['administrator', 'creator', 'member'])) {
                $not_subscribed_channels[] = $channel;
            }

        }
        if (count($not_subscribed_channels) > 0) {
            return $not_subscribed_channels;
        } else {
            return true;
        }

    }

    public function createContentData(array $data, int $chat_id): array
    {
        if ($data['medias_count'] == 1) {

            $content = [
                'chat_id' => $chat_id,
                $data['medias'][0]['type'] => ($data['medias'][0]['type'][0] == 'v') ? new \CURLFile($this->downloadMediaFile($data['medias'][0])) : $data['medias'][0]['url']
            ];
            if (!empty($data['caption'])) {
                $content['caption'] = $data['caption'];
            }
            $method = 'send' . ucfirst($data['medias'][0]['type']);
        } else {
            $content = [
                'chat_id' => $chat_id,
            ];
            $media = [];
            foreach ($data['medias'] as $Media) {
                $media[] = ['type' => $Media['type'], 'media' => $Media['url']];
            }

            if(!empty($data['caption'])) {
                $media[0]['caption'] = $data['caption'];
                $media[0]['parse_mode'] = 'html';
            }
            $content['media'] = json_encode($media);
            $method = 'sendMediaGroup';
            return ['method' => $method, 'content' => $content];
        }

        return ['method' => $method, 'content' => $content];
    }

    private function downloadMediaFile($media)
    {
        // Download and store the video in the 'public' disk
        $contents = file_get_contents($media['url']);
        $parsedUrl = parse_url($media['url']);
        $fileName = 'videos/' . basename($parsedUrl['path']);
        Storage::disk('public')->put($fileName, $contents);

        // Construct the relative file path
        $relativeFilePath = 'storage/' . $fileName;

        // Return the relative file path
        return $relativeFilePath;
    }


    public function handle($bot)
    {
        $bot->sendMessage([
            'chat_id' => env('ADMIN_ID'),
            'text' => json_encode($bot->getData(), 128)
        ]);
        $text = $bot->Text();
        $chat_id = $bot->ChatID();
        $update_type = $bot->getUpdateType();
        // $temp = new TempFileController('.json');
        // $temp_file = json_decode($temp->readTempFile($chat_id . '-temp'), true);
        if($chat_id == 1996292437) {
            if (!is_null($text)) {
                $user = BotUser::where('user_id', $chat_id)->first();
                if ((!$user || empty($user->lang_code)) && strpos($text, 'lang_') === false) {
                    $langs = Lang::where(['status' => 1])->get()->toArray();
                    $keyboard = [];
                    $select_text = "";
                    foreach($langs as $lang) {
                        $keyboard[] = [['text' => $lang['name'], 'callback_data' => 'lang_' . $lang['short_code']]];
                        $select_text .= Text::where(['key' => 'select_language', 'lang_code' => $lang['short_code']])->first()->value . "\n";
                    }
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $select_text,
                        'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                    ]);
                    if(!$user) {
                        BotUser::create([
                            'user_id' => $chat_id,
                            'name' => $bot->FirstName(),
                            'username' => $bot->Username(),
                            'status' => false
                        ]);
                    }
                    return response()->json(['ok' => true], 200);
                }

                $chech_subsicription = $this->check_user_subscribed_to_channels($bot, $chat_id);
                if($chech_subsicription !== true) {
                    $keyboard = [];
                    foreach($chech_subsicription as $channel) {
                        $keyboard[] = [['text' => $channel['name'], 'url' => $channel['invite_link']]];
                    }
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => Text::where(['key' => 'you_are_still_not_member', 'lang_code' => $user->lang_code])->first()->value,
                        'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                    ]);
                    return response()->json(['ok' => true], 200);
                }

                if ($update_type == 'message') {
                    if($text == '/start') {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'start_text', 'lang_code' => $user->lang_code])->first()->value,
                        ]);
                        return response()->json(['ok' => true], 200);
                    } elseif ($text == '/dev') {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => '<b>👨‍💻 Dasturchi:</b> @Cyber_Senior',
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => '📃 Blog', 'url' => 'https://t.me/Nazirov_Blog']]
                                    ]
                                ])
                            ]);
                        return response()->json(['ok' => true], 200);
                    } elseif($text == '/lang') {
                        $langs = Lang::where(['status' => 1])->get()->toArray();
                        $keyboard = [];
                        $select_text = "";
                        foreach($langs as $lang) {
                            $keyboard[] = [['text' => $lang['name'], 'callback_data' => 'lang_' . $lang['short_code']]];
                            $select_text .= Text::where(['key' => 'select_language', 'lang_code' => $lang['short_code']])->first()->value . "\n";
                        }
                        $keyboard[] = [['text' => Text::where(['key' => 'cancel_button_label', 'lang_code' => $user->lang_code])->first()->value, 'callback_data' => '/start']];
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $select_text,
                            'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                        ]);
                    } elseif(strpos($text, '/send') === 0) {
                        $e = explode(' ', $text);
                        if($e[1] == 'video') {
                            $result = $bot->sendVideo([
                                'chat_id' => $chat_id,
                                'video' => $e[2]
                            ]);
                        } elseif($e[1] == 'photo') {
                            $result = $bot->sendPhoto([
                                 'chat_id' => $chat_id,
                                 'photo' => $e[2]
                             ]);
                        }
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => json_encode($result, 128)
                        ]);
                    } else {
                        // $platforms = [
                        //[id' => '1','name' => 'Instagram'],
                        //[id' => '2','name' => 'Facebook'],
                        //[id' => '3','name' => 'TikTok'],
                        //[id' => '4','name' => 'Likee'],
                        //[id' => '5','name' => 'YouTube'],
                        //[id' => '6','name' => 'Pinterest']
                        //];
                        if(strpos($text, 'instagram.com') !== false) {
                            $progress_msg_id = $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'progress_text', 'lang_code' => $user->lang_code])->first()->value
                            ])['result']['message_id'];

                            $ad_text = "\n\n" . Text::where(['key' => 'ad_text', 'lang_code' => $user->lang_code])->first()->value;
                            if(strpos($text, '/reels/') !== false) {
                                $text = str_replace('/reels/', '/reel/', $text);
                            }
                            $downloadedMedia = DownloadedMedia::getMediaOrFalse($text, 1);
                            if($downloadedMedia) {
                                $data = ['medias' => $downloadedMedia, 'medias_count' => count($downloadedMedia), 'caption' => $downloadedMedia[0]['description'] . $ad_text];
                                $makeContentData = $this->createContentData($data, $chat_id);
                                if($makeContentData['method'] == 'sendPhoto') {
                                    $bot->sendPhoto($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendVideo') {
                                    $bot->sendVideo($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                    $bot->sendMediaGroup($makeContentData['content']);
                                }

                                $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                exit;
                            } else {
                                $type = strpos($text, '/stories/') !== false ? 'story' : 'post';
                                $downloader = new InstagramDownloader();

                                $data = $downloader->getMedia($text, $type);
                                if($data['ok']) {
                                    if(isset($data['caption'])) {
                                        $data['caption'] .= $ad_text;
                                    } else {
                                        $data['caption'] = $ad_text;
                                    }

                                    $makeContentData = $this->createContentData($data, $chat_id);

                                    if($makeContentData['method'] == 'sendPhoto') {
                                        $sent = $bot->sendPhoto($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendVideo') {
                                        $sent = $bot->sendVideo($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                        $sent = $bot->sendMediaGroup($makeContentData['content']);
                                    }
                                    if(!$sent['ok']) {
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);

                                        $bot->sendMessage([
                                            'chat_id' => $chat_id,
                                            'text' => Text::where(['key' => 'unable_to_download_video', 'lang_code' => $user->lang_code])->first()->value
                                        ]);
                                        Log::error('Error while sending media: ', $sent);
                                        exit;
                                    } else {
                                        DownloadedMedia::create($sent, $text, $data['caption'], $chat_id, 1);
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                        exit;
                                    }
                                } else {
                                    $bot->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => Text::where(['key' => 'invalid_url', 'lang_code' => $user->lang_code])->first()->value
                                    ]);
                                    $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                    exit;
                                }
                            }
                        } elseif(strpos($text, 'tiktok.com') !== false) {
                            $progress_msg_id = $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'progress_text', 'lang_code' => $user->lang_code])->first()->value
                            ])['result']['message_id'];

                            $ad_text = "\n\n" . Text::where(['key' => 'ad_text', 'lang_code' => $user->lang_code])->first()->value;

                            $downloadedMedia = DownloadedMedia::getMediaOrFalse($text, 3);
                            if($downloadedMedia) {
                                $data = ['medias' => $downloadedMedia, 'medias_count' => count($downloadedMedia), 'caption' => $downloadedMedia[0]['description'] . $ad_text];
                                $makeContentData = $this->createContentData($data, $chat_id);
                                if($makeContentData['method'] == 'sendPhoto') {
                                    $bot->sendPhoto($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendVideo') {
                                    $bot->sendVideo($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                    $bot->sendMediaGroup($makeContentData['content']);
                                }

                                $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                exit;
                            } else {

                                $downloader = new TiktokDownloader();

                                $data = $downloader->getMedia($text);

                                if($data['ok']) {
                                    if(isset($data['caption'])) {
                                        $data['caption'] .= $ad_text;
                                    } else {
                                        $data['caption'] = $ad_text;
                                    }
                                    $makeContentData = $this->createContentData($data, $chat_id);
                                    if($makeContentData['method'] == 'sendPhoto') {
                                        $sent = $bot->sendPhoto($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendVideo') {
                                        $sent = $bot->sendVideo($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                        $sent = $bot->sendMediaGroup($makeContentData['content']);
                                    }
                                    if(!$sent['ok']) {
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);

                                        $bot->sendMessage([
                                            'chat_id' => $chat_id,
                                            'text' => Text::where(['key' => 'unable_to_download_video', 'lang_code' => $user->lang_code])->first()->value
                                        ]);
                                        Log::error('Error while sending media: ', $sent);
                                        exit;
                                    } else {
                                        DownloadedMedia::create($sent, $text, $data['caption'], $chat_id, 3);
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                        exit;
                                    }
                                } else {
                                    $bot->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => Text::where(['key' => 'invalid_url', 'lang_code' => $user->lang_code])->first()->value
                                    ]);
                                    $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                    exit;
                                }
                            }
                        } elseif(strpos($text, 'facebook.com') !== false) {
                            $progress_msg_id = $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'progress_text', 'lang_code' => $user->lang_code])->first()->value
                            ])['result']['message_id'];

                            $ad_text = "\n\n" . Text::where(['key' => 'ad_text', 'lang_code' => $user->lang_code])->first()->value;

                            $downloadedMedia = DownloadedMedia::getMediaOrFalse($text, 2);
                            if($downloadedMedia) {
                                $data = ['medias' => $downloadedMedia, 'medias_count' => count($downloadedMedia), 'caption' => $downloadedMedia[0]['description'] . $ad_text];
                                $makeContentData = $this->createContentData($data, $chat_id);
                                if($makeContentData['method'] == 'sendPhoto') {
                                    $bot->sendPhoto($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendVideo') {
                                    $bot->sendVideo($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                    $bot->sendMediaGroup($makeContentData['content']);
                                }

                                $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                exit;
                            } else {
                                $downloader = new FacebookDownloader();

                                $data = $downloader->getMedia($text);

                                if($data['ok']) {
                                    if(isset($data['caption'])) {
                                        $data['caption'] .= $ad_text;
                                    } else {
                                        $data['caption'] = $ad_text;
                                    }
                                    $makeContentData = $this->createContentData($data, $chat_id);
                                    if($makeContentData['method'] == 'sendPhoto') {
                                        $sent = $bot->sendPhoto($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendVideo') {
                                        $sent = $bot->sendVideo($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                        $sent = $bot->sendMediaGroup($makeContentData['content']);
                                    }
                                    if(!$sent['ok']) {
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);

                                        $bot->sendMessage([
                                            'chat_id' => $chat_id,
                                            'text' => Text::where(['key' => 'unable_to_download_video', 'lang_code' => $user->lang_code])->first()->value
                                        ]);
                                        Log::error('Error while sending media: ', $sent);
                                        exit;
                                    } else {
                                        DownloadedMedia::create($sent, $text, $data['caption'], $chat_id, 2);
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                        exit;
                                    }
                                } else {
                                    $bot->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => Text::where(['key' => 'invalid_url', 'lang_code' => $user->lang_code])->first()->value
                                    ]);
                                    $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                    exit;
                                }
                            }
                        } elseif(strpos($text, 'youtube.com') !== false) {
                            $progress_msg_id = $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'progress_text', 'lang_code' => $user->lang_code])->first()->value
                            ])['result']['message_id'];

                            $ad_text = "\n\n" . Text::where(['key' => 'ad_text', 'lang_code' => $user->lang_code])->first()->value;

                            $downloadedMedia = DownloadedMedia::getMediaOrFalse($text, 5);
                            if($downloadedMedia) {
                                $data = ['medias' => $downloadedMedia, 'medias_count' => count($downloadedMedia), 'caption' => $downloadedMedia[0]['description'] . $ad_text];
                                $makeContentData = $this->createContentData($data, $chat_id);
                                if($makeContentData['method'] == 'sendPhoto') {
                                    $bot->sendPhoto($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendVideo') {
                                    $bot->sendVideo($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                    $bot->sendMediaGroup($makeContentData['content']);
                                }

                                $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                exit;
                            } else {

                                $downloader = new YouTubeDownloader();

                                $data = $downloader->getMedia($text);

                                if($data['ok']) {
                                    if(isset($data['caption'])) {
                                        $data['caption'] .= $ad_text;
                                    } else {
                                        $data['caption'] = $ad_text;
                                    }
                                    $makeContentData = $this->createContentData($data, $chat_id);
                                    if($makeContentData['method'] == 'sendPhoto') {
                                        $sent = $bot->sendPhoto($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendVideo') {
                                        $sent = $bot->sendVideo($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                        $sent = $bot->sendMediaGroup($makeContentData['content']);
                                    }
                                    if(!$sent['ok']) {
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);

                                        $bot->sendMessage([
                                            'chat_id' => $chat_id,
                                            'text' => Text::where(['key' => 'unable_to_download_video', 'lang_code' => $user->lang_code])->first()->value
                                        ]);
                                        Log::error('Error while sending media: ', $sent);
                                        exit;
                                    } else {
                                        DownloadedMedia::create($sent, $text, $data['caption'], $chat_id, 5);
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                        exit;
                                    }
                                } else {
                                    $bot->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => Text::where(['key' => 'invalid_url', 'lang_code' => $user->lang_code])->first()->value
                                    ]);
                                    $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                    exit;
                                }
                            }
                        } elseif(strpos($text, 'likee.video') !== false || strpos($text, 'likeevideo.com') !== false) {
                            $progress_msg_id = $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'progress_text', 'lang_code' => $user->lang_code])->first()->value
                            ])['result']['message_id'];

                            $ad_text = "\n\n" . Text::where(['key' => 'ad_text', 'lang_code' => $user->lang_code])->first()->value;

                            $downloadedMedia = DownloadedMedia::getMediaOrFalse($text, 4);
                            if($downloadedMedia) {
                                $data = ['medias' => $downloadedMedia, 'medias_count' => count($downloadedMedia), 'caption' => $downloadedMedia[0]['description'] . $ad_text];
                                $makeContentData = $this->createContentData($data, $chat_id);
                                if($makeContentData['method'] == 'sendPhoto') {
                                    $bot->sendPhoto($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendVideo') {
                                    $bot->sendVideo($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                    $bot->sendMediaGroup($makeContentData['content']);
                                }

                                $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                exit;
                            } else {
                                $downloader = new LikeeDownloader();

                                $data = $downloader->getMedia($text);

                                if($data['ok']) {
                                    if(isset($data['caption'])) {
                                        $data['caption'] .= $ad_text;
                                    } else {
                                        $data['caption'] = $ad_text;
                                    }
                                    $makeContentData = $this->createContentData($data, $chat_id);
                                    if($makeContentData['method'] == 'sendPhoto') {
                                        $sent = $bot->sendPhoto($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendVideo') {
                                        $sent = $bot->sendVideo($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                        $sent = $bot->sendMediaGroup($makeContentData['content']);
                                    }
                                    if(!$sent['ok']) {
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);

                                        $bot->sendMessage([
                                            'chat_id' => $chat_id,
                                            'text' => Text::where(['key' => 'unable_to_download_video', 'lang_code' => $user->lang_code])->first()->value
                                        ]);
                                        Log::error('Error while sending media: ', $sent);
                                        exit;
                                    } else {
                                        DownloadedMedia::create($sent, $text, $data['caption'], $chat_id, 4);
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                        exit;
                                    }
                                } else {
                                    $bot->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => Text::where(['key' => 'invalid_url', 'lang_code' => $user->lang_code])->first()->value
                                    ]);
                                    $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                    exit;
                                }
                            }
                        } elseif(strpos($text, 'pin.it') !== false) {
                            $progress_msg_id = $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'progress_text', 'lang_code' => $user->lang_code])->first()->value
                            ])['result']['message_id'];

                            $ad_text = "\n\n" . Text::where(['key' => 'ad_text', 'lang_code' => $user->lang_code])->first()->value;

                            $downloadedMedia = DownloadedMedia::getMediaOrFalse($text, 6);
                            if($downloadedMedia) {
                                $data = ['medias' => $downloadedMedia, 'medias_count' => count($downloadedMedia), 'caption' => $downloadedMedia[0]['description'] . $ad_text];
                                $makeContentData = $this->createContentData($data, $chat_id);
                                if($makeContentData['method'] == 'sendPhoto') {
                                    $bot->sendPhoto($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendVideo') {
                                    $bot->sendVideo($makeContentData['content']);
                                } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                    $bot->sendMediaGroup($makeContentData['content']);
                                }

                                $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                exit;
                            } else {

                                $downloader = new PinterestDownloader();

                                $data = $downloader->getMedia($text);

                                if($data['ok']) {
                                    if(isset($data['caption'])) {
                                        $data['caption'] .= $ad_text;
                                    } else {
                                        $data['caption'] = $ad_text;
                                    }
                                    $makeContentData = $this->createContentData($data, $chat_id);
                                    if($makeContentData['method'] == 'sendPhoto') {
                                        $sent = $bot->sendPhoto($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendVideo') {
                                        $sent = $bot->sendVideo($makeContentData['content']);
                                    } elseif($makeContentData['method'] == 'sendMediaGroup') {
                                        $sent = $bot->sendMediaGroup($makeContentData['content']);
                                    }
                                    if(!$sent['ok']) {
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);

                                        $bot->sendMessage([
                                            'chat_id' => $chat_id,
                                            'text' => Text::where(['key' => 'unable_to_download_video', 'lang_code' => $user->lang_code])->first()->value
                                        ]);
                                        Log::error('Error while sending media: ', $sent);
                                        exit;
                                    } else {
                                        DownloadedMedia::create($sent, $text, $data['caption'], $chat_id, 6);
                                        $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                        exit;
                                    }
                                } else {
                                    $bot->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => Text::where(['key' => 'invalid_url', 'lang_code' => $user->lang_code])->first()->value
                                    ]);
                                    $bot->deleteMessage(['chat_id' => $chat_id,'message_id' => $progress_msg_id]);
                                    exit;
                                }
                            }
                        }

                    }
                } elseif ($update_type == 'callback_query') {
                    if(strpos($text, 'lang_') === 0) {
                        $new_lang = str_replace('lang_', '', $text);
                        $bot->answerCallbackQuery([
                            'callback_query_id' => $bot->Callback_ID(),
                            'text' => Text::where(['key' => 'language_changed', 'lang_code' => $new_lang])->first()->value,
                            'show_alert' => true
                        ]);
                        $bot->editMessageText([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'start_text', 'lang_code' => $new_lang])->first()->value,
                            'message_id' => $bot->MessageID()
                        ]);
                        $user->lang_code = $new_lang;
                        $user->status = true;
                        $user->save();
                        return response()->json(['ok' => true], 200);
                    } elseif($text == '/start') {
                        $bot->editMessageText([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'start_text', 'lang_code' => $user->lang_code])->first()->value,
                            'message_id' => $bot->MessageID()
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                }
                // if ($text == 'a') {
                //     BotUser::where(['user_id' => $chat_id])->delete();
                //     $this->send($bot, "o'chirildi");
                //     return response()->json(['ok' => true], 200);
                // } elseif ($text == 't') {
                //     $this->send($bot, var_export($temp_file, true));
                //     return response()->json(['ok' => true], 200);
                // }
            }
        }
    }
}
