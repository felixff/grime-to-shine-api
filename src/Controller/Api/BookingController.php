<?php

namespace GTS\Api\Controller\Api;

use DateTime;
use Exception;
use GTS\Api\Model\Booking;
use GTS\Api\Utils\Email;

class BookingController extends BaseController
{
    /**
     * "/booking/list" Endpoint - Get list of bookings
     */
    public function listAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
//        $arrQueryStringParams = $this->getQueryStringParams();
        $strErrorHeader = '';
        $responseData = 'Nothing to see here';

        if (strtoupper($requestMethod) == 'GET') {
            try {
                $bookingModel = new Booking();
                $bookings = $bookingModel->getBookings();

                $responseData = json_encode($bookings);
            } catch (Exception $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    public function addAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $requestData = $_POST;
        $strErrorHeader = '';
        $responseData = 'Nothing to see here';

        if (strtoupper($requestMethod) == 'POST') {
            try {
                $bookingModel = new Booking();
                $booking = $bookingModel->addBooking($requestData);
                $bookingStart = $booking['start']->getDateTime();
                $bookingContact = $requestData['name'];
                $bookingContactTelephone = $requestData['telephone'];
                $bookingService = $requestData['serviceLevel'];
                $this->sendRequestEmail($bookingContact, $bookingService, $bookingContactTelephone, $bookingStart);

                $responseData = json_encode($booking);
            } catch (Exception $e) {
                $strErrorDesc = $e->getMessage() . '. Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
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
        $email->setRecipients(['Kieran' => 'faragau.florin+dev@gmail.com']);
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