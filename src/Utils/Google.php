<?php

namespace GTS\Api\Utils;

use DateTimeInterface;
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

    public function getEvents(): array
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
        $response = [];

        foreach ($events as $event) {
            $eventToReturn = new \stdClass();
            $eventToReturn->date = (new \DateTime($event->getStart()->dateTime))->format('d-m-Y');

            if (isset($response[$eventToReturn->date]) === false) {
                $response[$eventToReturn->date] = [];
            }

            $response[$eventToReturn->date][] = (new \DateTime($event->getStart()->dateTime))->format('H:m');
        }

        if (empty($events)) {
            return [];
        } else {
            return $response;
        }
    }

    public function addEvent($event)
    {
        // Get the API client and construct the service object.
        $service = new \Google_Service_Calendar($this->client);

        $calendarId = 'primary';
        $time = explode(':', $event['time']);
        $startDate = (new \DateTime($event['date'],new \DateTimeZone('Europe/London')))->setTime((int)$time[0], (int)$time[1]);
        $endDate = ((new \DateTime($event['date'],new \DateTimeZone('Europe/London')))->setTime((int)$time[0], (int)$time[1]))->add(new \DateInterval('PT30M'));

        $startCalendarDateTime = new \Google_Service_Calendar_EventDateTime();
        $startCalendarDateTime->setDateTime($startDate->format(DateTimeInterface::RFC3339));
        $endCalendarDateTime = new \Google_Service_Calendar_EventDateTime();
        $endCalendarDateTime->setDateTime($endDate->format(DateTimeInterface::RFC3339));

        $optParams = array(
            'summary' => 'Car Valeting',
            'location' => $event['address'],
            'description' => true,
            'start' => $startCalendarDateTime,
            'end' => $endCalendarDateTime
        );

        $event = new \Google_Service_Calendar_Event($optParams);
        $event = $service->events->insert($calendarId, $event);

        if (empty($event)) {
            throw new \Exception('Booking has failed');
        } else {
            return $event;
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