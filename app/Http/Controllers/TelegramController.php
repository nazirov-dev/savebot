<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Http\Controllers\PrivateChat;
use App\Http\Controllers\ChannelController;


class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        $input = $request->all();
        $bot = new TelegramService;
        // $bot->sendMessage([
        //     'chat_id' => 1996292437,
        //     'text' => json_encode($bot->getData(), 128)
        // ]);

        if (isset($input['message']))
            $chat_type = $input['message']['chat']['type'] ?? null;
        elseif (isset($input['callback_query']))
            $chat_type = $input['callback_query']['message']['chat']['type'] ?? null;
        else{
            exit;
        }

        if ($chat_type == 'private') {
            $run = new PrivateChat();
            return $run->handle($bot);
        }
    }
}
