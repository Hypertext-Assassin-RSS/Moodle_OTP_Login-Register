<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_register_button';

function local_register_button_extend_signup_form(MoodleQuickForm $form) {
    global $PAGE;
    $PAGE->requires->js('/local/register_button/register_button.js');

    $button_html = '<button id="register_button" type="button">'.get_string('register_button', 'local_register_button').'</button>';
    $form->addElement('html', $button_html);
}
