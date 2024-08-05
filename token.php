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

// Check if user exists with the provided username and mobile number
$sql = "SELECT u.id
        FROM {user} u
        JOIN {user_info_data} uid ON u.id = uid.userid
        WHERE u.deleted = 0 AND u.suspended = 0
        AND u.username = :username
        AND uid.fieldid = :fieldid
        AND uid.data = :mobile_number";

$params = array('username' => $username, 'fieldid' => 6, 'mobile_number' => $mobileNumber);

$user = $DB->get_record_sql($sql, $params);

if ($user) {
    echo json_encode(['result' => 'Success' , 'user' => $user]);
} else {
    echo json_encode(['result' => 'User with this username and mobile number does not exist']);
}
