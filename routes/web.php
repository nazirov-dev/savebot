<?php

use App\Jobs\SendMessageToAllUsers;
use App\Models\Channel;
use App\Models\NotificationStatus;
use App\Models\Questionnair;
use App\Models\Text;
use App\Models\TgFileId;

use App\Models\Variant;
use App\Services\TelegramService;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
// use Symfony\Component\Process\Process;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/questionnaire/{id}/result', function ($id) {
    $questionnair = Questionnair::find($id);
    if (!$questionnair) {
        return redirect()->back()->with('error', 'So\'rovnoma topilmadi!');
    } else {
        $variants = Variant::where(['questionnaire_id' => $questionnair->id])->get();
        $results = [];
        $totalVotes = Variant::where(['questionnaire_id' => $questionnair->id])->sum('votes_count');
        foreach ($variants as $variant) {
            $results[] = [
                'variant' => $variant->value,
                'percentage' => $variant->votes_count != 0 ? ($variant->votes_count / $totalVotes) *  100 : 0,
                'votes_count' => $variant->votes_count,
            ];
        }
        $results['description'] = $questionnair->description ?? 'Description';
        $results['name'] = $questionnair->name ?? '';
        return json_encode($results, 128);
    }
})->name('questionnaire.result');
Route::post('/send-post-to-channels', function (Request $request) {
    function make_variants_keyboard($variants, $url, $button_text)
    {
        return array_map(function ($variant) use ($url, $button_text) {
            return [
                [
                    'text' => str_replace(
                        ['{answer}', '{answers_count}'],
                        [$variant['value'], $variant['votes_count']],
                        $button_text
                    ),
                    'url' => $url
                ]
            ];
        }, $variants);
    }

    $data = $request->all();
    $bot = new TelegramService();
    $vote = Questionnair::where('id', $data['questionnaire_id'])->where('status', 1)->first();

    if (is_null($vote)) {
        return response()->json(['ok' => true], 200);
    }

    $variants = Variant::where('questionnaire_id', $vote->id)->get()->toArray();
    $button_text = Text::where('key', 'answer_button')->value('value');
    $question_url = Text::where('key', 'question_url')->value('value');
    $text = $vote->description;
    // $distance = ($data['distance'] ?? 0) * 60;


    foreach ($data['channels'] as $channel) {
        $id = explode('{}', $channel)[0];
        $chat_id = Channel::where('id', $id)->value('channel_id');
        $url = "https://t.me/Namangan2023_bot?start=vote={$vote['unique_key']}_ch={$id}";

        if ($vote->type == 'text') {
            $message_id = $bot->sendMessage([
                'chat_id' => $chat_id,
                'text' => "{$text}\n\n <a href=\"{$url}\">{$question_url}</a>",
                'reply_markup' => $bot->buildInlineKeyBoard(make_variants_keyboard($variants, $url, $button_text))
            ])['result']['message_id'];
        } elseif ($vote->type == 'media') {
            $file_id = TgFileId::where('id', explode('.', $vote->media)[0])->value('file_id');
            $message_id = $bot->sendPhoto([
                'chat_id' => $chat_id,
                'photo' => $file_id,
                'caption' => "{$text}\n\n <a href=\"{$url}\">{$question_url}</a>",
                'reply_markup' => $bot->buildInlineKeyBoard(make_variants_keyboard($variants, $url, $button_text))
            ])['result']['message_id'];
        }
        $vote->last_posted_channel = json_encode(['channel_id'=>$chat_id,'message_id'=>$message_id]);
    }

    $vote->save();
    return response()->json(['ok' => true], 200);
})->name('sendpost');

Route::post('/send-notification', function (Request $request) {
    $data = $request->all();
    SendMessageToAllUsers::dispatch($data);
    // $process = new Process(['php', 'artisan', 'queue:work'], timeout: 0);
    // $process->start();
    // litespeed_finish_request();
    Artisan::call('queue:work --stop-when-empty');
    $bot = new TelegramService();
    $bot->sendMessage([
        'chat_id'=>env('ADMIN_ID'),
        'text'=>Artisan::output(). var_export($data, true)
    ]);

    return response()->json(['ok' => true], 200);
})->name('send-notification');
Route::get('/stop-bulk-send', function () {
    $n = NotificationStatus::where(['id' => 1])->first();
    $n->status = 'stopped';
    $n->log = json_encode(['message' => "Xabar yuborish admin tomonidan majburan to'xtalildi."]);
    $n->save();
    return response()->json(["ok" => true], 200);
})->name('stop-sending');
