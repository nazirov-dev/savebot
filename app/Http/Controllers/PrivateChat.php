<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TempFileController;

use App\Models\TgFileId;
use Illuminate\Support\Facades\DB;
use App\Models\BotUser;
use App\Models\Text;
use App\Models\Questionnair;
use App\Models\Vote;
use App\Models\Variant;
use App\Models\Channel;


class PrivateChat extends Controller
{
    public function __construct()
    {
    }
    public function check_user_subscribed_to_channels($bot, $channels_id, $user_id)
    {
        $not_subscribed_channels = [];
        foreach ($channels_id as $id) {
            $channel = DB::table('channels')->where(['id' => $id, 'status' => 1])->first();
            if (!is_null($channel)) {
                $status = $bot->getChatMember([
                    'chat_id' => $channel->channel_id,
                    'user_id' => $user_id
                ])['result']['status'];
                if (!in_array($status, ['administrator', 'creator', 'member'])) {
                    $not_subscribed_channels[] = $channel;
                }
            }
        }
        if (count($not_subscribed_channels) > 0)
            return $not_subscribed_channels;
        else
            return true;
    }
    public function handle($bot)
    {
        $text = $bot->Text();
        $chat_id = $bot->ChatID();
        $update_type = $bot->getUpdateType();
        $temp = new TempFileController('.json');
        $temp_file = json_decode($temp->readTempFile($chat_id . '-temp'), true);
        // if ($temp_file['success']) $this->send($bot, $text . " You have temporary file:" . PHP_EOL . var_export($temp_file, true));

        if (!is_null($text)) {
            if ($update_type == 'callback_query') {
                if ($temp_file['success']) {
                    $json = json_decode($temp_file['content'], true);
                    if ($text == 'continue' and isset($json['update'])) {
                        $bot->deleteMessage([
                            'chat_id' => $chat_id,
                            'message_id' => $bot->MessageID()
                        ]);
                        $bot->setData($json['update']);
                        return $this->handle($bot);
                    }
                }
                if (strpos($text, 'check_') === 0) {
                    $vote = Questionnair::where(['id' => str_replace('check_', '', $text), 'status' => 1])->first();
                    if (is_null($vote)) {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'vote_not_found'])->first()['value']
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                    $check = $this->check_user_subscribed_to_channels(
                        $bot,
                        $vote['connected_channels'],
                        $chat_id
                    );
                    $bot->deleteThisMessage();
                    if ($check === true) {
                        $variants = Variant::where(['questionnaire_id' => $vote['id']])->get()->toArray();
                        $keyboard = [];
                        $button_text = Text::where(['key' => 'answer_button'])->first()['value'];
                        foreach ($variants as $variant) {
                            $keyboard[] = [['text' => str_replace(['{answer}', '{answers_count}'], [$variant['value'], $variant['votes_count']], $button_text), 'callback_data' => 'vote_' . $variant['id']]];
                        }
                        if ($vote['type'] == 'text') {
                            $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => $vote['description'],
                                'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                            ]);
                            return response()->json(['ok' => true], 200);
                        } elseif ($vote['type'] == 'media') {
                            $bot->sendPhoto([
                                'chat_id' => $chat_id,
                                'photo' =>  TgFileId::where(['id' => explode('.', $vote['media'])[0]])->first()['file_id'],
                                'caption' => $vote['description'],
                                'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                            ]);
                            return response()->json(['ok' => true], 200);
                        }
                    } else {
                        $keyboard = [];
                        foreach ($check as $channel) {
                            $keyboard[] = [['text' => $channel->name, 'url' => $channel->url]];
                        }
                        $keyboard[] =  [['text' => Text::where(['key' => 'subscribed_button'])->first()['value'], 'callback_data' => 'check_' . $vote['id']]];
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'not_subscribed'])->first()['value'],
                            'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                }
                if (strpos($text, 'vote_') === 0) {
                    $variant = Variant::where(['id' => str_replace('vote_', '', $text)])->first();
                    if (is_null($variant)) {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where('key', 'variant_not_found')->first()['value']
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                    $vote = Questionnair::where(['id' => $variant['questionnaire_id'], 'status' => 1])->first();
                    if (is_null($vote)) {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'vote_not_found'])->first()['value']
                        ]);
                        return response()->json(['ok' => true], 200);
                    } else {
                        $hasUserVotedBefore = Vote::where(['user_id' => $chat_id, 'questionnaire_id' => $vote['id']])->first();
                        if (is_null($hasUserVotedBefore)) {
                            if (!$temp_file['success']) {
                                $captcha = new CaptchaController();
                                $captcha->sendViaTelegram($bot, $chat_id, Text::where('key', 'enter_captcha')->first()['value']);
                                $temp_file['content']['captcha'] = $captcha->getCaptchaCode();
                                $temp_file['content']['update'] =  $bot->getData();
                                $temp->createTempFile($chat_id . '-temp', json_encode($temp_file['content']), true);
                                return response()->json(['ok' => true], 200);
                            } else {
                                Vote::create([
                                    'user_id' => $chat_id,
                                    'questionnaire_id' => $vote['id'],
                                    'variant_id' => $variant['id'],
                                    'time' => now()
                                ]);
                                $variant->increment('votes_count');
                                $notif_text = str_replace('{answer}', $variant['value'], Text::where(['key' => 'answered'])->first()['value']);
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => $notif_text,
                                    'show_alert' => true,
                                    'cache_time' => 15
                                ]);
                                $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => $notif_text,
                                    'reply_to_message_id' => $bot->MessageID()
                                ]);

                                $variants = Variant::where(['questionnaire_id' => $vote['id']])->get()->toArray();
                                $keyboard = [];
                                $button_text = Text::where(['key' => 'answer_button'])->first()['value'];
                                foreach ($variants as $variant) {
                                    $keyboard[] = [['text' => str_replace(['{answer}', '{answers_count}'], [$variant['value'], $variant['votes_count']], $button_text), 'callback_data' => 'vote_' . $variant['id']]];
                                }
                                $bot->editMessageReplyMarkup([
                                    'chat_id' => $chat_id,
                                    'message_id' => $bot->MessageID(),
                                    'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                                ]);
                                if ($vote['last_posted_channel'] != '{}' and !is_null($vote['last_posted_channel'])) {
                                    $last_posted_channel = json_decode($vote['last_posted_channel'], true);
                                    $id = Channel::where('channel_id', $last_posted_channel['channel_id'])->first()->id;
                                    $url = "https://t.me/Namangan2023_bot?start=vote={$vote['unique_key']}_ch={$id}";
                                    $keyboard2 = [];
                                    foreach ($variants as $variant) {
                                        $keyboard2[] = [['text' => str_replace(['{answer}', '{answers_count}'], [$variant['value'], $variant['votes_count']], $button_text), 'url' => $url]];
                                    }
                                    $bot->editMessageReplyMarkup([
                                        'chat_id' => $last_posted_channel['channel_id'],
                                        'message_id' => $last_posted_channel['message_id'],
                                        'reply_markup' => $bot->buildInlineKeyBoard($keyboard2)
                                    ]);
                                }
                                $temp->deleteTempFile($chat_id . '-temp');
                                return response()->json(['ok' => true], 200);
                            }
                        } else {
                            $user_s_variant =  Variant::where(['id' => $hasUserVotedBefore['variant_id']])->first();
                            $bot->answerCallbackQuery([
                                'callback_query_id' => $bot->Callback_ID(),
                                'text' => str_replace('{answer}', $user_s_variant['value'], Text::where(['key' => 'allready_answered'])->first()['value']),
                                'show_alert' => true,
                                'cache_time' => 15
                            ]);
                            return response()->json(['ok' => true], 200);
                        }
                    }
                }
                if (strpos($text, 'select_') === 0) {
                    $bot->deleteThisMessage();
                    $ex = explode('_', $text);
                    $vote = Questionnair::where(['id' =>  $ex[1], 'status' => 1])->first();
                    if (is_null($vote)) {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'vote_not_found'])->first()['value']
                        ]);
                        return response()->json(['ok' => true], 200);
                    } else {
                        $check = $this->check_user_subscribed_to_channels(
                            $bot,
                            $vote['connected_channels'],
                            $chat_id
                        );
                        $bot->deleteThisMessage();
                        if ($check === true) {
                            $variants = Variant::where(['questionnaire_id' => $vote['id']])->get()->toArray();
                            $keyboard = [];
                            $button_text = Text::where(['key' => 'answer_button'])->first()['value'];
                            foreach ($variants as $variant) {
                                $keyboard[] = [['text' => str_replace(['{answer}', '{answers_count}'], [$variant['value'], $variant['votes_count']], $button_text), 'callback_data' => 'vote_' . $variant['id']]];
                            }
                            if ($vote['type'] == 'text') {
                                $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => $vote['description'],
                                    'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                                ]);
                                return response()->json(['ok' => true], 200);
                            } elseif ($vote['type'] == 'media') {
                                $bot->sendPhoto([
                                    'chat_id' => $chat_id,
                                    'photo' =>  TgFileId::where(['id' => explode('.', $vote['media'])[0]])->first()['file_id'],
                                    'caption' => $vote['description'],
                                    'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                                ]);
                                return response()->json(['ok' => true], 200);
                            }
                        } else {
                            $keyboard = [];
                            foreach ($check as $channel) {
                                $keyboard[] = [['text' => $channel->name, 'url' => $channel->url]];
                            }
                            $keyboard[] =  [['text' => Text::where(['key' => 'subscribed_button'])->first()['value'], 'callback_data' => 'check_' . $vote['id']]];
                            $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'not_subscribed'])->first()['value'],
                                'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                            ]);
                            return response()->json(['ok' => true], 200);
                        }
                    }
                }
            } elseif ($update_type == 'message') {
                if ($temp_file['success']) {
                    $json = json_decode($temp_file['content'], true);
                    if (isset($json['captcha']) and is_numeric($text)) {
                        if ($text == $json['captcha']) {
                            $json['captcha_solved'] = true;
                            unset($json['captcha']);
                            $temp->createTempFile($chat_id . '-temp', json_encode($json), true);
                            $bot->setData($json['update']);
                            return $this->handle($bot);
                        } else {
                            $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'incorrect_captcha_code'])->first()['value']
                            ]);
                            return response()->json(['ok' => true], 200);
                        }
                    }
                }
                if (strpos($text, '/start') === 0) {
                    // kelgan buyruq /start komandasi bilan boshlanganini tekshirish uchun
                    $user = BotUser::where(['user_id' => $chat_id])->first();
                    if (is_null($user) and !isset($json['phone_number'])) {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'enter_phone_number'])->first()['value'],
                            'reply_markup' => $bot->buildKeyBoard([
                                [['text' => Text::where(['key' => 'send_phone_number_btn'])->first()['value'], 'request_contact' => true]]
                            ], true, true)
                        ]);
                        $temp->createTempFile($chat_id . '-temp', json_encode(['step' => 'input_phone_number', 'update' => $bot->getData()]));
                        return response()->json(['ok' => true], 200);
                    }
                    if ($text == '/start') {
                        if (is_null($user)) {
                            BotUser::create([
                                'user_id' => $chat_id,
                                'fullname' => $bot->FirstName() . ' ' . $bot->LastName(),
                                'username' => $bot->Username() ?? '',
                                'phone_number' => ($temp_file['success'] and isset($json['phone_number'])) ? $json['phone_number'] : 'Kiritilmagan',
                                'registered_at' => now(),
                                'last_action' => now(),
                                'offered_channel_id' => 0
                            ]);
                        }
                        $temp->deleteTempFile($chat_id . '-temp');

                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'start_txt'])->first()['value'],
                            'reply_markup' => $bot->buildKeyBoard([
                                [['text' => Text::where(['key' => 'vote_btn'])->first()['value']]]
                            ], true, true)
                        ]);
                        return response()->json(['ok' => true], 200);
                    } else {
                        $ex = explode('_', explode(' ', $text)[1]); // /start vote=2_ch=2
                        if (is_null($user)) {
                            BotUser::create([
                                'user_id' => $chat_id,
                                'fullname' => $bot->FirstName() . ' ' . $bot->LastName(),
                                'username' => $bot->Username() ?? '',
                                'phone_number' => ($temp_file['success'] and isset($json['phone_number'])) ? $json['phone_number'] : 'Kiritilmagan',
                                'registered_at' => now(),
                                'last_action' => now(),
                                'offered_channel_id' => (count($ex) == 2) ? str_replace('ch=', '', $ex[1]) : 0
                            ]);
                        }
                        $temp->deleteTempFile($chat_id . '-temp');
                        $vote = Questionnair::where(['unique_key' => str_replace('vote=', '', $ex[0]), 'status' => 1])->first();
                        if (is_null($vote)) {
                            $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'vote_not_found'])->first()['value']
                            ]);
                            return response()->json(['ok' => true], 200);
                        } else {
                            $check = $this->check_user_subscribed_to_channels(
                                $bot,
                                $vote['connected_channels'],
                                $chat_id
                            );
                            $bot->deleteThisMessage();
                            if ($check === true) {
                                $variants = Variant::where(['questionnaire_id' => $vote['id']])->get()->toArray();
                                $keyboard = [];
                                $button_text = Text::where(['key' => 'answer_button'])->first()['value'];
                                foreach ($variants as $variant) {
                                    $keyboard[] = [['text' => str_replace(['{answer}', '{answers_count}'], [$variant['value'], $variant['votes_count']], $button_text), 'callback_data' => 'vote_' . $variant['id']]];
                                }
                                if ($vote['type'] == 'text') {
                                    $bot->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => $vote['description'],
                                        'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                                    ]);
                                    return response()->json(['ok' => true], 200);
                                } elseif ($vote['type'] == 'media') {
                                    $bot->sendPhoto([
                                        'chat_id' => $chat_id,
                                        'photo' =>  TgFileId::where(['id' => explode('.', $vote['media'])[0]])->first()['file_id'],
                                        'caption' => $vote['description'],
                                        'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                                    ]);
                                    return response()->json(['ok' => true], 200);
                                }
                            } else {
                                $keyboard = [];
                                foreach ($check as $channel) {
                                    $keyboard[] = [['text' => $channel->name, 'url' => $channel->url]];
                                }
                                $keyboard[] =  [['text' => Text::where(['key' => 'subscribed_button'])->first()['value'], 'callback_data' => 'check_' . $vote['id']]];
                                $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => Text::where(['key' => 'not_subscribed'])->first()['value'],
                                    'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                                ]);
                                return response()->json(['ok' => true], 200);
                            }
                        }
                    }
                }
                if ($text == Text::where(['key' => 'vote_btn'])->first()['value']) {
                    $keyboard = [];
                    $questionnaires = Questionnair::where(['status' => 1])->get()->toArray();
                    if (empty($questionnaires)) {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::where(['key' => 'no_active_questionnaires'])->first()['value'],
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                    foreach ($questionnaires as $questionnaire) {
                        $keyboard[] = [['text' => $questionnaire['name'], 'callback_data' => 'select_' . $questionnaire['id']]];
                    }
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => Text::where(['key' => 'select_questionnaire'])->first()['value'],
                        'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                    ]);
                    return response()->json(['ok' => true], 200);
                }
                if ($text == '/dev') {
                    $bot->sendMessage([
                        'chat_id'=>$chat_id,
                        'text'=>'<b>👨‍💻 Dasturchi:</b> @Cyber_Senior',
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [['text'=>'📃 Blog', 'url'=>'https://t.me/Nazirov_Blog']]
                                ]
                            ])
                        ]);
                        return response()->json(['ok' => true], 200);
                }
            } elseif ($update_type == 'contact') {
                if ($temp_file['success']) {
                    $json = json_decode($temp_file['content'], true);
                    if (isset($json['step']) and $json['step'] == 'input_phone_number') {
                        if ($bot->getContactUserId() == $chat_id) {
                            if (boolval(preg_match('/^\+?998[-\s]?\d{2}[-\s]?\d{3}[-\s]?\d{2}[-\s]?\d{2}$/', $text))) {
                                $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => Text::where(['key' => 'phone_number_received'])->first()['value'],
                                    'reply_markup' => $bot->buildInlineKeyBoard([
                                        [['text' => Text::where(['key' => 'continue_btn'])->first()['value'], 'callback_data' => 'continue']]
                                    ])
                                ]);
                                unset($json['step']);
                                $json['phone_number'] = $text;
                                $temp->createTempFile($chat_id . '-temp', json_encode($json), true);
                                return response()->json(['ok' => true], 200);
                            } else {
                                $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => Text::where(['key' => 'phone_number_invalid'])->first()['value']
                                ]);
                                return response()->json(['ok' => true], 200);
                            }
                        } else {
                            $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::where(['key' => 'only_enter_your_number'])->first()['value']
                            ]);
                            return response()->json(['ok' => true], 200);
                        }
                    }
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
