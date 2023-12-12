<?php

namespace App\Http\Controllers;


use CURLFile;
use GuzzleHttp\Client;

class CaptchaController extends Controller
{
    private $captcha;
    private $image;

    public function __construct()
    {
        $this->generateCaptcha();
    }

    private function generateCaptcha()
    {
        $this->captcha = rand(1000, 9999);
        $this->image = imagecreatefrompng(resource_path('assets/captcha6.png'));

        $fg = imagecolorallocate($this->image, 0, 0, 0);

        $font =  resource_path('assets/code2002.ttf');  // Ensure this font file exists in the same directory or provide the correct path
        imagefttext($this->image, 80, 0, 110, 160, $fg, $font, $this->captcha);
    }

    public function getCaptchaCode()
    {
        return $this->captcha;
    }

    public function getCaptchaImage()
    {
        return $this->image;
    }

    public function outputImage()
    {
        header('Content-type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
    }

    public function saveImage($filename)
    {
        imagejpeg($this->image, $filename);
        imagedestroy($this->image);
        return $filename;
    }

    public function sendViaTelegram($bot, $chat_id, $caption)
    {
        // Save the image temporarily
        $tempFile = sys_get_temp_dir() . '/' . uniqid('captcha_', true) . '.png';
        $this->saveImage($tempFile);

        // Send the image using Telegram bot
        $chatId = 1996292437;
        $token  = $bot->getBotToken();
        $client = new Client(['verify' => false]);  // 'verify' => false is important for local env without valid SSL
        $url = "https://api.telegram.org/bot{$token}/sendPhoto";

        $response = $client->post($url, [
            'multipart' => [
                [
                    'name'     => 'chat_id',
                    'contents' => $chat_id
                ],
                [
                    'name' => 'caption',
                    'contents' => $caption
                ],
                [
                    'name'     => 'photo',
                    'contents' => fopen($tempFile, 'r'),
                    'filename' => basename($tempFile)
                ]
            ]
        ]);

        // Delete the temporary file after sending
        unlink($tempFile);
    }


    // Example usage:
    // $captchaGenerator = new CaptchaGenerator();

    // echo "Captcha Code: " . $captchaGenerator->getCaptchaCode() . "\n";

    // To output the image directly to the browser
    //$captchaGenerator->outputImage();

    // To save the image to a file
    // $captchaGenerator->saveImage('filename.jpg');

}
