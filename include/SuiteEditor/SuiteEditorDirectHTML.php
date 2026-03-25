<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SuiteEditorDirectHTML
 *
 * use simple textarea as a SuiteEditor
 */
class SuiteEditorDirectHTML implements SuiteEditorInterface
{
    /**
     * @var SuiteEditorSettings $settings
     */
    protected $settings;

    /**
     * see more at SuiteEditorInterface
     *
     * @param SuiteEditorSettings $settings
     */
    public function setup(SuiteEditorSettings $settings = null)
    {
        $this->settings = $settings;
    }

    /**
     * see more at SuiteEditorInterface
     *
     * @return mixed
     */
    public function getHtml()
    {
        $smarty = new Sugar_Smarty();
        $smarty->assign((array)$this->settings);
        return $smarty->fetch(get_custom_file_if_exists('include/SuiteEditor/tpls/SuiteEditorDirectHTML.tpl'));
    }
}
