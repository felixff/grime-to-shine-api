<?php
namespace GTS\Api\Model;


use GTS\Api\Utils\Google;

class Booking
{
    public function getBookings()
    {
        return Google::getInstance()->getEvents();
    }

    public function addBooking()
    {
        return false;
    }
}