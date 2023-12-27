<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Notification;
use App\Models\NotificationStatus;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $bot = new TelegramService();
        $message = $bot->forwardMessage([
            'chat_id' => env('ADMIN_ID', 1996292437),
            'from_chat_id' => env('ADS_TELEGRAM_CHANNEL_ID'),
            'message_id' => $data['message_id']
        ]);
        $NotificationStatus = NotificationStatus::find(1);

        if($data['sending_type'] == 'copymessage' && !empty($message['result']['reply_markup'])) {
            $data['keyboard'] = base64_encode(json_encode($message['result']['reply_markup']));
        }
        $data['admin_info_message_id'] = null;

        if($NotificationStatus->status) {
            $data['status'] = 'waiting';
            $data['admin_info_message_id'] = null;
        } else {
            $data['status'] = 'sending';

            $info_message = $bot->sendMessage([
                'chat_id' => $data['admin_chat_id'],
                'text' => "<b>Xabarni yuborish 1 daqiqadan so'ng boshlanadi ✅</b>\n\n<i><b>Ushbu xabarni o'chirmang!!!</b></i>"
            ]);

            $data['admin_info_message_id'] = $info_message['result']['message_id'];

            $NotificationStatus->status = true;
            $NotificationStatus->notification_id = DB::selectOne(
                "SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name = ? AND table_schema = DATABASE()",
                ['notifications']
            )->AUTO_INCREMENT + 1;
            $NotificationStatus->sent = 0;
            $NotificationStatus->not_sent = 0;
            $NotificationStatus->last_user_index = 0;
            $NotificationStatus->telegram_retry_after_seconds = 0;
            $NotificationStatus->save();
        }
        $data['sent'] = 0;
        $data['not_sent'] = 0;
        $data['sending_end_time'] = null;
        return $data;
    }
}
