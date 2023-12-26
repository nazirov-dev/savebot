<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serverga yuklangan va allaqachon foydalanib bo\'lingan fayllarni o\'chiruvchi buyruq.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = 'videos'; // Directory inside storage/app/public
        $files = Storage::disk('public')->files($directory);

        foreach ($files as $file) {
            $filePath = 'public/' . $file;
            if (Storage::exists($filePath)) {
                $lastModified = Storage::lastModified($filePath);
                $lastModifiedTime = Carbon::createFromTimestamp($lastModified);

                if ($lastModifiedTime->lessThan(now()->subMinutes(10))) {
                    Storage::delete($filePath);
                }
            }
        }

    }
}
