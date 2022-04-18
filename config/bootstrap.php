<?php
const PROJECT_ROOT_PATH = __DIR__ . "/../src/";

// include main configuration file
require_once "config.php";

// include the base controller file
require_once PROJECT_ROOT_PATH . "/Controller/Api/BaseController.php";

// include the use model file
require_once PROJECT_ROOT_PATH . "/Model/Booking.php";