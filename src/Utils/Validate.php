<?php

namespace GTS\Api\Utils;

use Exception;
use ReflectionClass;

class Validate
{
    private const BOOKING_ACTION = [
        'date',
        'time',
        'name',
        'email',
        'telephone',
        'address',
        'postcode',
        'serviceLevel'
    ];

    /**
     * @throws Exception
     */
    static function validateAndSanitiseParameters(string $action, object $parametersObject): object
    {
        $r = new ReflectionClass(Validate::class);
        $parameters = $r->getConstant($action);

        if (empty($parameters)) {
            throw new Exception('Invalid action');
        }

        foreach ($parameters as $parameter) {
            if (isset($parametersObject->$parameter) === false) {
                throw new Exception('Invalid request', 400);
            }

            $parametersObject->$parameter = htmlspecialchars($parametersObject->$parameter);
        }

        return $parametersObject;
    }
}