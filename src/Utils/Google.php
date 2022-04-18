<?php

namespace GTS\Api\Utils;

use Google_Service_Calendar;

class Google
{
    protected $client;
    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Google();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        $this->client = new \Google_Client();
        $this->client->setApplicationName('grime-to-shine');
        $this->client->setAuthConfig(__DIR__ . '/../../credentials/client_secret.json');
        $this->client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');

        if (file_exists(__DIR__ . '/../../token.json')) {
            $this->setAccessToken();
            $this->refreshToken();
        } else {
            throw new \Exception('Google Integration has not been setup');
        }
    }

    public function getEvents()
    {
        // Get the API client and construct the service object.
        $service = new \Google_Service_Calendar($this->client);

        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        if (empty($events)) {
            print "No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($events as $event) {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                printf("%s (%s)\n", $event->getSummary(), $start);
            }
        }
    }

    private function setAccessToken()
    {
        $accessToken = json_decode(file_get_contents(__DIR__ . '/../../token.json'), true);
        $this->client->setAccessToken($accessToken);
    }

    private function refreshToken()
    {
        // Refresh the token if it's expired.
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            file_put_contents(__DIR__ . '/../../token.json', json_encode($this->client->getAccessToken()));
        }
    }
}