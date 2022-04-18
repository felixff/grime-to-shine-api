<?php
namespace GTS\Api\Model;


use GTS\Api\Utils\Google;

class Booking
{
    public function getBookings(): array
    {
        return Google::getInstance()->getEvents();
    }

    public function addBooking($event)
    {
        return Google::getInstance()->addEvent($event);
    }
}