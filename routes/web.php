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


Route::get('/stop-sending-notification/{notification_id}', function ($notification_id) {
    $NotificationStatus = App\Models\NotificationStatus::find(1);
    $Notification = App\Models\Notification::find($notification_id);
    if($NotificationStatus->status and $NotificationStatus->notification_id == $notification_id) {
        $NotificationStatus->status = false;
        $NotificationStatus->save();
    }
    if($Notification->status == 'sending') {
        $Notification->status = 'terminated';
        $Notification->save();
    }
    return redirect()->route('filament.admin.pages.dashboard');
})->name('stop-sending-notification');

Route::post('/sendMedia', function (Request $request) {
    $fileId = $request->input('file_id');
    $type = $request->input('type');
    $caption = $request->input('description');

    $bot = new TelegramService();
    if($type == 'photo') {
        $sent = $bot->sendPhoto(['chat_id' => config('env.ADMIN_ID'), 'photo' => $fileId, 'caption' => $caption]);
    } elseif($type == 'video') {
        $sent = $bot->sendVideo(['chat_id' => config('env.ADMIN_ID'), 'video' => $fileId, 'caption' => $caption]);
    }
    return  response()->json(['ok' => $sent['ok']]);
})->name('sendMedia');


// Route::post('/send-notification', function (Request $request) {
//     $data = $request->all();
//     SendMessageToAllUsers::dispatch($data);
//     // $process = new Process(['php', 'artisan', 'queue:work'], timeout: 0);
//     // $process->start();
//     // litespeed_finish_request();
//     Artisan::call('queue:work --stop-when-empty');
//     $bot = new TelegramService();
//     $bot->sendMessage([
//         'chat_id' => config('env.ADMIN_ID'),
//         'text' => Artisan::output() . var_export($data, true)
//     ]);

//     return response()->json(['ok' => true], 200);
// })->name('send-notification');

// Route::get('/stop-bulk-send', function () {
//     $n = NotificationStatus::where(['id' => 1])->first();
//     $n->status = 'stopped';
//     $n->log = json_encode(['message' => "Xabar yuborish admin tomonidan majburan to'xtalildi."]);
//     $n->save();
//     return response()->json(["ok" => true], 200);
// })->name('stop-sending');
