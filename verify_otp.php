<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/register_button/lib.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Retrieve parameters
$username = required_param('username', PARAM_TEXT);
$mobileNumber = required_param('mobile_number', PARAM_TEXT);
$enteredOTP = required_param('otp', PARAM_TEXT);


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$storedOTP = isset($_SESSION['generated_otp']) ? $_SESSION['generated_otp'] : null;


if ($enteredOTP == $storedOTP) {
    $user = $DB->get_record('user', array('username' => $username));
    if ($user) {

        complete_user_login($user);

        unset($_SESSION['generated_otp']);

        echo json_encode(['result' => 'Success']);
        die();
    } else {
        echo json_encode(['result' => 'Username not found', 'otp' => $storedOTP]);
        die();
    }
} else {
    echo json_encode(['result' => 'Invalid OTP']);
    die();
}