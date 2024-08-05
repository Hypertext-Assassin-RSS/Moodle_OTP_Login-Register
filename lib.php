<?php
defined('MOODLE_INTERNAL') || die();


class local_register_button_observer {

    public static function user_created_manual($user) {
        error_log('User object: ' . print_r($user, true));
    
        global $DB;
        global $CFG;
    
        if (isset($user->profile_field_Phone)) {
            $mobilenumber = $user->profile_field_Phone;
            error_log('Mobile number: ' . $mobilenumber);
    

            $apikey = get_config('local_register_button', 'apikey');
            $sourceaddress = "";


            $activationlink = $CFG->wwwroot . '/login/confirm.php?data=' . $user->secret . '/' . $user->username;
            
            $message = 'Please click the following link to activate your account for Samanala eSchool:' . $activationlink;    
    
            // Send the SMS.
            $numberList = array($mobilenumber);
            $result = sendMessage($apikey, $numberList, $message, $sourceaddress);
    
            if ($result === 'Success') {
                error_log('SMS sent successfully.');
                return true;
            } else {
                error_log('Failed to send SMS.');
                return false;
            }
        } else {
            error_log('Mobile number not set in user profile.');
            return false;
        }
    }
    
}


function local_register_button_send_activation_sms($user) {
    global $DB;

    // Retrieve the API key and source address from settings.
    $apikey = get_config('local_register_button', 'apikey');
    $sourceAddress = "";


    // Generate the activation link.
    $activationlink = new moodle_url('/login/confirm.php', array('data' => $user->secret));
    $message = "Activate your account using this link: " . $activationlink->out(false);

    // Extract the mobile number from the user profile field.
    $mobilefieldid = $DB->get_field('user_info_field', 'id', array('shortname' => 'mobile')); // Adjust 'mobile' to your field shortname
    $mobilenumber = $DB->get_field('user_info_data', 'data', array('userid' => $user->id, 'fieldid' => $mobilefieldid));

    // Send the SMS.
    $numberList = array($mobilenumber);
    $result = sendMessage($apikey, $numberList, $message, $sourceaddress);
	
	self::purge_all_caches();

    return $result === 'Success';
}


function local_register_button_extend_signup_form(MoodleQuickForm $form)
{
    global $PAGE;
    $PAGE->requires->js('/local/register_button/register_button.js');
    $PAGE->requires->css('/local/register_button/styles.css');

    // Add mobile number validation
    $form->setType('profile_field_Phone', PARAM_NOTAGS);
    $form->addRule('profile_field_Phone', null, 'numeric', null, 'client');
    $form->addRule('profile_field_Phone', get_string('maximumchars', '', 10), 'maxlength', 10, 'client');


    // Add button
    $button_html = '<button id="register_button" type="button" class="btn btn-primary mt-3">'.get_string('register_button', 'local_register_button').'</button>';
    $form->addElement('html', $button_html);

    // Add hidden OTP input field and verify button
    $otp_html = '
        <div id="otp_section" style="display: none;">
            <input type="text" id="otp_input" class="form-control" placeholder="'.get_string('enter_otp', 'local_register_button').'">
            <button id="verify_button" class="btn btn-secondary m-3" type="button">'.get_string('verify_button', 'local_register_button').'</button>
        </div>';
    $form->addElement('html', $otp_html);
}

function sendMessage($apiKey, $numberList, $message, $sourceAddress) {
    $ch = curl_init();
    $list = implode(",", $numberList);
    $pushNotificationUrl = "";
    $url = "https://e-sms.dialog.lk/api/v1/message-via-url/create/url-campaign?esmsqk={$apiKey}&list={$list}&source_address={$sourceAddress}&message=" . urlencode($message) . "&push_notification_url=" . urlencode($pushNotificationUrl);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $result = 'Error:' . curl_error($ch);
    } else {
        switch (trim($response)) {
            case "1":
                $result = "Success";
                break;
            case "2001":
                $result = "Error occurred during campaign creation";
                break;
            case "2002":
                $result = "Bad request";
                break;
            case "2003":
                $result = "Empty number list";
                break;
            case "2004":
                $result = "Empty message body";
                break;
            case "2005":
                $result = "Invalid number list format";
                break;
            case "2006":
                $result = "Not eligible to send messages via GET requests (Admin hasn’t provided the access level)";
                break;
            case "2007":
                $result = "Invalid key (esmsqk parameter is invalid)";
                break;
            case "2008":
                $result = "Not enough money in the user's wallet or not enough messages left in the package for the user. (When consuming package payments)";
                break;
            case "2009":
                $result = "No valid numbers found after the removal of mask blocked numbers.";
                break;
            case "2010":
                $result = "Not eligible to consume packaging";
                break;
            case "2011":
                $result = "Transactional error";
                break;
            default:
                $result = "Unknown response: " . $response;
                break;
        }
    }
    curl_close($ch);
    return $result;
}

function local_register_button_send_message() {
    global $CFG;

    $apikey = get_config('local_register_button', 'apikey');
    $number = required_param('mobile_number', PARAM_TEXT);


    $otp = rand(10000, 99999);

    
        // Check if a session is already active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    $_SESSION['generated_otp'] = $otp;


    $message = "OTP for Login: {$otp}

Do not share this with anyone for security reasons.

Samanala eSchool";

    $sourceAddress = "";

    $response = sendMessage($apikey, [$number], $message, $sourceAddress);

    echo json_encode(['result' => $response , 'otp' => $otp]);
    die();
}


function local_register_button_extend_navigation_user_settings($settingsnav, $usernode) {
    global $PAGE;

    if ($PAGE->url->compare(new moodle_url('/login/signup.php'), URL_MATCH_BASE)) {
        local_register_button_extend_registration_form($mform);
    }
}

