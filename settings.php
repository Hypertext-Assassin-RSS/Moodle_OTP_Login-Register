<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_register_button', get_string('pluginname', 'local_register_button'));

    $settings->add(new admin_setting_configtext('local_register_button/apikey',
        get_string('apikey', 'local_register_button'), '', '', PARAM_TEXT));

    $ADMIN->add('localplugins', $settings);
}
