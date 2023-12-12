<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class GenerateFakeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:fakedata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample fake data for tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faker = Faker::create();

        // Fake data generators:

        function generateAdmin($faker)
        {
            return [
                'user_id' => $faker->unique()->randomNumber(),
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        function generateChannel($faker)
        {
            return [
                'channel_id' => $faker->unique()->randomNumber(),
                'username' => $faker->userName,
                'name' => $faker->company,
                'url' => $faker->url,
                'status' => $faker->boolean,
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        function generateFailedJob($faker)
        {
            return [
                'uuid' => $faker->uuid,
                'connection' => $faker->word,
                'queue' => $faker->word,
                'payload' => $faker->sentence,
                'exception' => $faker->sentence,
                'failed_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        // Skipping 'migrations' as it's usually managed by frameworks

        function generateNotificationStatus($faker)
        {
            return [
                'notification_id' => $faker->randomNumber(),
                'sent' => $faker->randomNumber(),
                'not_sent' => $faker->randomNumber(),
                'time' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'status' => $faker->word
            ];
        }

        function generateNotification($faker)
        {
            return [
                'text' => $faker->sentence,
                'media' => $faker->imageUrl(),
                'type' => $faker->randomElement(['text', 'image', 'video']),
                'status' => $faker->boolean,
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        function generateQuestionnaire($faker)
        {
            return [
                'media' => $faker->imageUrl(),
                'name' => $faker->word,
                'description' => $faker->sentence,
                'type' => $faker->randomElement(['text', 'image', 'video']),
                'connected_channels' => json_encode([$faker->word, $faker->word]),
                'unique_key' => $faker->unique()->word,
                'status' => $faker->boolean,
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        function generateText($faker)
        {
            return [
                'key' => $faker->word,
                'value' => $faker->sentence,
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        function generateUser($faker)
        {
            return [
                'user_id' => $faker->unique()->randomNumber(),
                'fullname' => $faker->name,
                'username' => $faker->userName,
                'phone_number' => $faker->phoneNumber,
                'registered_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'last_action' => $faker->dateTimeThisMonth->format('Y-m-d H:i:s'),
                'offered_channel_id' => $faker->randomNumber(),
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        function generateVariant($faker, $questionnaire_id)
        {
            return [
                'value' => $faker->word,
                'questionnaire_id' => $questionnaire_id,
                'votes_count' => $faker->randomNumber()
            ];
        }

        function generateVote($faker, $questionnaire_id)
        {
            return [
                'user_id' => $faker->randomNumber(),
                'questionnaire_id' => $questionnaire_id,
                'variant' => $faker->sentence,
                'time' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s')
            ];
        }

        // Generate and print 10 fake entries for each table as a sample
        // for ($i = 0; $i < 10; $i++) {
        //     print_r(generateUser($faker));
        //     print_r(generateChannel($faker));
        //     print_r(generateAdmin($faker));
        //     print_r(generateFailedJob($faker));
        //     print_r(generateNotificationStatus($faker));
        //     print_r(generateNotification($faker));
        //     print_r(generateQuestionnaire($faker));
        //     print_r(generateText($faker));

        //     $questionnaire_id = $faker->randomNumber();
        //     print_r(generateVariant($faker, $questionnaire_id));
        //     print_r(generateVote($faker, $questionnaire_id));
        // }
        for ($i = 0; $i < 10; $i++) {
            DB::table('users')->insert(generateUser($faker));
            DB::table('channels')->insert(generateChannel($faker));
            DB::table('admins')->insert(generateAdmin($faker));
            // DB::table('failed_jobs')->insert(generateFailedJob($faker));
            DB::table('notification_status')->insert(generateNotificationStatus($faker));
            DB::table('notifications')->insert(generateNotification($faker));
            DB::table('questionnaires')->insert(generateQuestionnaire($faker));
            DB::table('texts')->insert(generateText($faker));
        
            $questionnaire_id = $faker->randomNumber();
            DB::table('variants')->insert(generateVariant($faker, $questionnaire_id));
            DB::table('votes')->insert(generateVote($faker, $questionnaire_id));
        }
        
    }
}
