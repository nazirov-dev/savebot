<?php

use App\Models\Lang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Platform; // Import your Platform model

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define default platforms
        $platforms = [
            'Instagram',
            'Facebook',
            'TikTok',
            'Likee',
            'YouTube',
            'Pinterest',
        ];

        // Insert platforms into the database using the Platform model
        foreach ($platforms as $platformName) {
            Platform::create(['name' => $platformName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Your existing code for removing languages and texts...

        // Remove the inserted platforms using the Platform model
        Platform::whereIn('name', [
            'Instagram',
            'Facebook',
            'TikTok',
            'Likee',
            'YouTube',
            'Pinterest',
        ])->delete();
    }
};
