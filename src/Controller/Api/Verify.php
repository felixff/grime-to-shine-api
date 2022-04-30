<?php

namespace GTS\Api\Controller\Api;

use Exception;
use GTS\Api\Utils\Config;

class Verify extends BaseController
{
    protected string $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @throws Exception
     */
    public function verify(): bool
    {
        $secret = Config::getConfig()->clientSecret ?? '';
        $verifyResponse = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$this->token"));

        if ($verifyResponse->success === false || $verifyResponse->score < 0.5) {
            throw new Exception('Bye bye bot', 403);
        }

        return true;
    }
}