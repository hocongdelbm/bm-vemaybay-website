<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SuiteEditorMozaik
 *
 * Use Mozaik Editor as SuiteEditor
 */
class SuiteEditorMozaik implements SuiteEditorInterface
{
    /**
     * @var SuiteEditorSettings $settings
     */
    protected $settings;

    /**
     * @var SuiteMozaik $mozaik
     */
    protected $mozaik;

    /**
     * see at SuiteEditorInterface
     *
     * @param SuiteEditorSettings $settings
     */
    public function setup(SuiteEditorSettings $settings = null)
    {
        $this->settings = $settings;
        require_once('include/SuiteMozaik.php');
        $this->mozaik = new SuiteMozaik();
    }

    /**
     * see at SuiteEditorInterface
     *
     * @return mixed
     */
    public function getHtml()
    {
        $smarty = new Sugar_Smarty();
        $smarty->assign((array)$this->settings);
        $smarty->assign('mozaik', $this->mozaik->getAllHTML(
            $this->settings->contents,
            $this->settings->textareaId,
            $this->settings->elementId,
            $this->settings->width,
            $this->settings->group,
            $this->settings->tinyMCESetup
        ));
        return $smarty->fetch(get_custom_file_if_exists('include/SuiteEditor/tpls/SuiteEditorMozaik.tpl'));
    }
}
