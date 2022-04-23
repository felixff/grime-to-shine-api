<?php
namespace GTS\Api\Model;


use Exception;
use Google_Service_Calendar_Event;
use GTS\Api\Utils\Google;

class Booking
{
    /**
     * @throws Exception
     */
    public function getBookings(): \stdClass
    {
        return Google::getInstance()->getEvents();
    }

    /**
     * @throws Exception
     */
    public function addBooking($event): Google_Service_Calendar_Event
    {
        return Google::getInstance()->addEvent($event);
    }
}