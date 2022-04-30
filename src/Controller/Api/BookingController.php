<?php

namespace GTS\Api\Controller\Api;

use DateTime;
use Exception;
use GTS\Api\Model\Booking;
use GTS\Api\Utils\Email;
use stdClass;

class BookingController extends BaseController
{
    /**
     * "/booking/list" Endpoint - Get list of bookings
     */
    public function listAction()
    {
        $responseData = 'Nothing to see here';

        try {
            $bookingModel = new Booking();
            $bookings = $bookingModel->getBookings();

            $responseData = $bookings;
        } catch (Exception $e) {
            $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
        }

        if (!isset($strErrorDesc)) {
            return $responseData;
        } else {
            return ['error' => $strErrorDesc];
        }
    }

    public function bookEventAction(object $event)
    {
        $responseData = 'Nothing to see here';

        try {
            $bookingModel = new Booking();
            $booking = $bookingModel->addBooking($event);
            $bookingStart = $booking['start']->getDateTime();
            $bookingContact = $event->name;
            $bookingContactTelephone = $event->telephone;
            $bookingService = $event->serviceLevel;
            $this->sendRequestEmail($bookingContact, $bookingService, $bookingContactTelephone, $bookingStart);

            return $booking;
        } catch (Exception $e) {
            $strErrorDesc = $e->getMessage() . '. Something went wrong! Please contact support.';
        }

        if (!isset($strErrorDesc)) {
            return $responseData;
        } else {
            return ['error' => $strErrorDesc];
        }
    }

    /**
     * @throws Exception
     */
    private function sendConfirmationEmail($bookingContact, $bookingService, $bookingContactTelephone, $bookingStart)
    {

    }

    /**
     * @throws Exception
     */
    private function sendRequestEmail($bookingContact, $bookingService, $bookingContactTelephone, $bookingStart)
    {
        $bookingTime = new DateTime($bookingStart);
        $service = ucfirst($bookingService) . ' Service';

        $email = new Email();
        $email->setRecipients(['Kieran' => 'grimetoshinevaletingapp@gmail.com']);
        $email->setSubject('Booking Request Received');
        $email->setContent(
            "<h2>Hi Kieran!</h2><br>
                    <p>$bookingContact has requested a booking for a $service on {$bookingTime->format('d.m.Y')} at {$bookingTime->format('H:i')}</p><br>
                    <p>They provided the following telephone number: $bookingContactTelephone</p><br>
                    <p>Have a nice day!</p>
                    "
        );

        $email->send();
    }
}