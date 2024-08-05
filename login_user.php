<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/otp/lib.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Retrieve parameters
$username = required_param('username', PARAM_TEXT);


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

    $user = $DB->get_record('user', array('username' => $username));
    if ($user) {
        // Log in the user
        complete_user_login($user);


        echo json_encode(['result' => 'Success']);
        die();
    } else {
        echo json_encode(['result' => 'Username not found']);
        die();
    }
 
