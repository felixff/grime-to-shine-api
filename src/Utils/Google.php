<?php

namespace GTS\Api\Utils;

use DateTimeInterface;
use Exception;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

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
            throw new Exception('Google Integration has not been setup');
        }
    }

    /**
     * @throws Exception
     */
    public function getEvents(): \stdClass
    {
        $service = new \Google_Service_Calendar($this->client);

        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 100,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => (new \DateTime('now', new \DateTimeZone('Europe/London')))->add(new \DateInterval('P1D'))->setTime(0, 0)->format(DateTimeInterface::RFC3339)
        );

        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();
        $eventsToReturn = [];

        foreach ($events as $event) {
            $eventToReturn = new \stdClass();
            $eventToReturn->date = (new \DateTime($event->getStart()->dateTime))->format('Y-m-d');
            $eventToReturn->start = (new \DateTime($event->getStart()->dateTime))->format('H:i');
            $eventToReturn->end = (new \DateTime($event->getEnd()->dateTime))->format('H:i');

            if (isset($eventsToReturn[$eventToReturn->date]) === false) {
                $eventsToReturn[$eventToReturn->date] = [];
            }

            $eventsToReturn[$eventToReturn->date][] = $eventToReturn;
        }

        $response = new \stdClass();
        $response->existingBookings = $eventsToReturn;

        return $response;
    }

    /**
     * @throws Exception
     */
    public function addEvent($event): Google_Service_Calendar_Event
    {
        $service = new \Google_Service_Calendar($this->client);

        $calendarId = 'primary';
        $time = explode(':', $event['time']);
        $startDate = (new \DateTime($event['date'], new \DateTimeZone('Europe/London')))->setTime((int)$time[0], (int)$time[1]);
        $endDate = ((new \DateTime($event['date'], new \DateTimeZone('Europe/London')))->setTime((int)$time[0], (int)$time[1]));
        $serviceLevel = ucfirst($event['serviceLevel']);

        switch ($serviceLevel) {
            case 'Extra':
            case 'Bronze':
                $endDate->add(new \DateInterval('PT30M'));
                break;
            case 'Silver':
                $endDate->add(new \DateInterval('PT60M'));
                break;
            case 'Gold':
                $endDate->add(new \DateInterval('PT90M'));
                break;
        }

        $startCalendarDateTime = new \Google_Service_Calendar_EventDateTime();
        $startCalendarDateTime->setDateTime($startDate->format(DateTimeInterface::RFC3339));
        $startCalendarDateTime->setTimeZone('UTC');
        $endCalendarDateTime = new \Google_Service_Calendar_EventDateTime();
        $endCalendarDateTime->setDateTime($endDate->format(DateTimeInterface::RFC3339));
        $endCalendarDateTime->setTimeZone('UTC');

        $optParams = array(
            'summary' => 'Booking Request',
            'location' => "{$event['address']}, {$event['postcode']}",
            'description' => "Name: {$event['name']}" . PHP_EOL . "Address: {$event['address']}" . PHP_EOL . "Service Level: {$serviceLevel}" . PHP_EOL . "Telephone: {$event['telephone']}" . PHP_EOL . "Email: {$event['email']}" . PHP_EOL . $event['message'],
            'start' => $startCalendarDateTime,
            'end' => $endCalendarDateTime
        );

        $event = new \Google_Service_Calendar_Event($optParams);
        $event = $service->events->insert($calendarId, $event);

        if (empty($event)) {
            throw new Exception('Booking has failed');
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