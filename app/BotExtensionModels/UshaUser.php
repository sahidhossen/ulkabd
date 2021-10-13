<?php

namespace App\BotExtensionModels;

class UshaUser
{
    private $external_id;
    private $platform;
    private $end_user;

    public function __construct($external_id, $platform, $end_user) {
        $this->external_id = $external_id;
        $this->platform = $platform;
        $this->end_user = $end_user;
    }

    public function userData() {
        $data = [
            'system_id' => $this->end_user->id,
            'external_id' => $this->external_id,
            'extension_platform' => $this->platform,
            'profile_img_url' => $this->end_user->profile_pic
        ];

        return $data;
    }
}
