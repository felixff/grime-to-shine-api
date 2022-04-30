<?php

namespace GTS\Api\Utils;

class Config
{
    public static function googleClientId()
    {
        return '197192523163-3cfsu3s3fp54f7s6kti3if5vuf01lcsn.apps.googleusercontent.com';
    }

    public static function getCredentials()
    {
        return __DIR__ . '/../../credentials/client_secret.json';
    }

    public static function getToken()
    {
        return __DIR__ . '/../../token.json';
    }

    public static function getConfig()
    {
        return json_decode(file_get_contents(__DIR__ . '/../../config.json'));
    }
}