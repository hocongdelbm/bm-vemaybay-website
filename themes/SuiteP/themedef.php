<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$themedef = array(
    'name' => 'Suite P',
    'description' => 'SuiteCRM Responsive Theme',
    'version' => array(
        'regex_matches' => array('.+'),
    ),
    'group_tabs' => true,
    'classic' => true,
    'configurable' => true,
    'config_options' => array(

        'display_sidebar' => array(
            'vname' => 'LBL_DISPLAY_SIDEBAR',
            'type' => 'bool',
            'default' => true,
        ),
        'sub_themes' => array(
            'vname' => 'LBL_SUBTHEME_OPTIONS',
            'type' => 'select',
            'default' => 'Dawn',
        ),
    ),
);

if (!empty($app_strings['LBL_SUBTHEMES'])) {
    // if statement removes the php notice
    $themedef['config_options']['sub_themes']['options'] = array(
        $app_strings['LBL_SUBTHEMES'] => array(
            'Dawn'  => $app_strings['LBL_SUBTHEME_OPTIONS_DAWN'],
            'Day'   => $app_strings['LBL_SUBTHEME_OPTIONS_DAY'],
            'Dusk'  => $app_strings['LBL_SUBTHEME_OPTIONS_DUSK'],
            'Night' => $app_strings['LBL_SUBTHEME_OPTIONS_NIGHT'],
            'Noon' => $app_strings['LBL_SUBTHEME_OPTIONS_NOON'],
        ),
    );
    $themedef['config_options']['sub_themes']['default'] = 'Dawn';
}
