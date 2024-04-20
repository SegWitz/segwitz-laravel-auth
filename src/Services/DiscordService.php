<?php
namespace Segwitz\Auth\Services;

use yashveersingh\laravelDiscordNotifier\DiscordNotifier;

class DiscordService{
    public static function sendOtp($message){
        $discordNotifier = new DiscordNotifier();
        $discordNotifier->setUserName(env('APP_NAME'));
        $discordNotifier->send($message);

        return true;
    }
}
