<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorInterface.php');
include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorSettings.php');
include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorSettingsForDirectHTML.php');
include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorDirectHTML.php');
include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorSettingsForTinyMCE.php');
include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorTinyMCE.php');
include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorSettingsForMozaik.php');
include_once get_custom_file_if_exists('include/SuiteEditor/SuiteEditorMozaik.php');

/**
 * Class SuiteEditor
 *
 * User Preference Editor connector class for different kind of editors
 * typically for Email Templates but any HTML or text editing..
 */
class SuiteEditorConnector
{
    public static function getSuiteSettings($html, $width)
    {
        return array(
            'contents' => $html,
            'textareaId' => 'body_text',
            'elementId' => 'email_template_editor',
            'width' => $width,
            'clickHandler' => "function(e){
                onClickTemplateBody();
            }",
            'tinyMCESetup' => "{
                setup: function(editor) {
                    editor.on('focus', function(e){
                        onClickTemplateBody();
                    });
                },
                height : '480',
                plugins: ['code', 'table', 'link', 'image'],
                toolbar: ['fontselect | fontsizeselect | bold italic underline | forecolor backcolor | styleselect | outdent indent | link image'],
                convert_urls: false,
            }"
        );
    }

    /**
     * return an output HTML of user selected editor for templates
     * based on current user preferences
     *
     * @param null $settings (optional) extends of selected editor default settings
     * @throws Exception unknown or incorrect editor
     * @return string HTML output of editor
     */
    public static function getHtml($settings = null)
    {
        global $current_user;

        switch ($current_user->getEditorType()) {

            case 'none':
                $editor = new SuiteEditorDirectHTML();
                $settings = new SuiteEditorSettingsForDirectHTML($settings);
                break;

            case 'tinymce':
                $editor = new SuiteEditorTinyMCE();
                $settings = new SuiteEditorSettingsForTinyMCE($settings);
                break;

            case 'mozaik':
                $editor = new SuiteEditorMozaik();
                $settings = new SuiteEditorSettingsForMozaik($settings);
                break;

            // new editor type should be possible to store in
            // user preferences but in this file for
            // add more type use the syntax bellow... for e.g:
            //
            //case 'your_awesome_editor':
            //    $editor = new SuiteEditorAwesome(); // where the editor class should implements SuiteEditorInterface
            //    break;

            default:
                throw new Exception('unknown editor type: ' . $current_user->getEditorType());
        }

        // just make sure the type of editor implements a SuiteEditorInterface..

        if (!($editor instanceof SuiteEditorInterface)) {
            throw new Exception("class $editor is not a SuiteEditorInterface");
        }

        // rendering the editor output HTML

        $editor->setup($settings);

        $smarty = new Sugar_Smarty();
        $smarty->assign('editor', $editor->getHtml());
        return $smarty->fetch(get_custom_file_if_exists('include/SuiteEditor/tpls/SuiteEditorConnector.tpl'));
    }
}
