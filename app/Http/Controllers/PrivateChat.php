<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TempFileController;

use Illuminate\Support\Facades\DB;
use App\Models\BotUser;
use App\Models\Text;
use App\Models\Lang;
use App\Models\Channel;

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
    public function handle($bot)
    {
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
                if ($update_type == 'callback_query') {
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
                    }
                } elseif ($update_type == 'message') {
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
