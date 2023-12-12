<?php

use App\Models\Lang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the Uzbek language to the langs table if it doesn't exist
        $uzbekLang = Lang::where('short_code', 'uz')->first();

        if (!$uzbekLang) {
            Lang::create([
                'name' => 'O\'zbek tili 🇺🇿',
                'short_code' => 'uz',
                'status' => 1
            ]);
        }

        // Define the default text translations
        $defaultTexts = [
            ['key' => 'start_text', 'value' => "<b>🤖 Assalomu aleykum hurmatli foydalanuvchi, Bot orqali siz yuklab olishingiz mumkin:</b>

• Instagram - stories, post va IGTV;
• YouTube - video formatda;
• TikTok - video;
• Likee - video;
• Pinterest - rasm, video va gif;
• Facebook - video;

<b>🚀 Media yuklash uchun shunchaki uning havolasini yuboring:</b>"],
            ['key' => 'subscribe_to_forced_channels', 'value' => "⚠️ Ushbu botdan foydalanish uchun quyidagi kanalga a’zo bo‘ling. Keyin <b>\"{check_button}\"</b> tugmasini bosing."],
            ['key' => 'ad_text', 'value' => '🤖 @ALLSAVEUZ_Bot orqali yuklab olindi.'],
            ['key' => 'language_changed', 'value' => 'Til o\'zgartirildi ✅'],
            ['key' => 'you_are_still_not_member', 'value' => 'Siz hali a\'zolikka o\'tmagansiz'],
            ['key' => 'check_button_label', 'value' => 'A’zo bo‘ldim ✅'],
            ['key' => 'cancel_button_label', 'value' => 'Bekor qilish ❌']
        ];


        // Seed the texts table for the Uzbek language
        if ($uzbekLang || ($uzbekLang = DB::table('langs')->where('short_code', 'uz')->first())) {
            foreach ($defaultTexts as $text) {
                DB::table('texts')->insert([
                    'key' => $text['key'],
                    'value' => $text['value'],
                    'lang_code' => $uzbekLang->short_code,
                ]);
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the inserted data from the 'texts' table
        DB::table('texts')->whereIn('key', [
            'start_text',
            'subscribe_to_forced_channels',
            'ad_text',
            'language_changed',
            'you_are_still_not_member',
            'check_button_label',
            'cancel_button_label',
        ])->delete();

        // Delete the Uzbek language from the 'langs' table if it was added by 'up' method
        DB::table('langs')->where('short_code', 'uz')->delete();
    }
};
