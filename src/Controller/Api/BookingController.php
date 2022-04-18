<?php
namespace GTS\Api\Controller\Api;

use Error;
use GTS\Api\Model\Booking;

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
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
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
                $bookings = $bookingModel->addBooking($requestData);

                $responseData = json_encode($bookings);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
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
}