<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/EditView/EditView2.php');
/**
 * Quick create form in the subpanel
 * @api
 */
class SubpanelQuickCreate
{
    public $defaultProcess = true;

    /**
     * The view type to use
     *
     * @var string
     */
    public $viewType = 'QuickCreate';

    public function __construct($module, $view = 'QuickCreate', $proccessOverride = false)
    {
        $this->viewType = $view;

        //treat quickedit and quickcreate views as the same
        if ($this->viewType == 'QuickEdit') {
            $this->viewType = 'QuickCreate';
        }

        // Get the viewdefs source file, called here to ensure proper viewType setting
        $source = $this->getModuleViewDefsSourceFile($module, $this->viewType);

        // locate the best viewdefs to use: 1. custom/module/quickcreatedefs.php 2. module/quickcreatedefs.php 3. custom/module/editviewdefs.php 4. module/editviewdefs.php
        $base = 'modules/' . $module . '/metadata/';
        $source = 'custom/' . $base . strtolower($view) . 'defs.php';
        if (!file_exists($source)) {
            $source = $base . strtolower($view) . 'defs.php';
            if (!file_exists($source)) {
                //if our view does not exist default to EditView
                $view = 'EditView';
                $source = 'custom/' . $base . 'editviewdefs.php';
                if (!file_exists($source)) {
                    $source = $base . 'editviewdefs.php';
                }
            }
        }

        $this->ev = $this->getEditView();
        $this->ev->view = $this->viewType;
        $this->ev->showVCRControl = false;
        $this->ev->ss = new Sugar_Smarty();
        //$_REQUEST['return_action'] = 'SubPanelViewer';

        $class = $GLOBALS['beanList'][$module];
        $bean = new $class();
        if (!empty($_REQUEST['record'])) {
            $bean->retrieve($_REQUEST['record']);
        }
        $this->ev->setup($module, $bean, $source);
        unset($bean);


        // Bug 49219 - Check empty before set defaults, or the settings from viewdefs above will be overridden.
        if (!isset($this->ev->defs['templateMeta']['form']['headerTpl'])) {
            $this->ev->defs['templateMeta']['form']['headerTpl'] = 'include/EditView/header.tpl';
        }

        if (!isset($this->ev->defs['templateMeta']['form']['footerTpl'])) {
            $this->ev->defs['templateMeta']['form']['footerTpl'] = 'include/EditView/footer.tpl';
        }
        // Comment below, breaks many out of the box viewdefs
        /*if (empty($this->ev->defs['templateMeta']['form']['buttons'])) $this->ev->defs['templateMeta']['form']['buttons'] = array('SUBPANELSAVE', 'SUBPANELCANCEL', 'SUBPANELFULLFORM');*/
        $this->ev->defs['templateMeta']['form']['buttons'] = array('SUBPANELSAVE', 'SUBPANELCANCEL', 'SUBPANELFULLFORM');

        //Load the parent view class if it exists.  Check for custom file first
        loadParentView('edit');

        $viewEditSource = 'modules/' . $module . '/views/view.edit.php';
        if (file_exists('custom/' . $viewEditSource)) {
            $viewEditSource = 'custom/' . $viewEditSource;
        }

        if (file_exists($viewEditSource) && !$proccessOverride) {
            include($viewEditSource);
            $c = $module . 'ViewEdit';

            $customClass = 'Custom' . $c;
            if (class_exists($customClass)) {
                $c = $customClass;
            }

            if (class_exists($c)) {
                $view = new $c;
                if ($view->useForSubpanel) {
                    $this->defaultProcess = false;

                    // Check if we should use the module's QuickCreate.tpl file.
                    if ($view->useModuleQuickCreateTemplate && file_exists('modules/' . $module . '/tpls/QuickCreate.tpl')) {
                        $this->ev->defs['templateMeta']['form']['headerTpl'] = 'modules/' . $module . '/tpls/QuickCreate.tpl';
                    }

                    $view->ev = &$this->ev;
                    $view->ss = &$this->ev->ss;
                    $class = $GLOBALS['beanList'][$module];
                    if (!empty($GLOBALS['beanFiles'][$class])) {
                        require_once($GLOBALS['beanFiles'][$class]);
                        $bean = new $class();
                        $view->bean = $bean;
                    }
                    $this->ev->formName = 'form_Subpanel' . $this->ev->view . '_' . $module;
                    $view->showTitle = false; // Do not show title since this is for subpanel
                    $view->display();
                }
            }
        } //if

        if ($this->defaultProcess && !$proccessOverride) {
            $this->process($module);
        }
    }

    public function process($module)
    {
        if ($_REQUEST['target_action'] == 'QuickCreate') {
            $this->ev->view = 'QuickCreate';
        }
        $form_name = 'form_Subpanel' . $this->ev->view . '_' . $module;
        $this->ev->formName = $form_name;
        $this->ev->process(true, $form_name);
        echo $this->ev->display(false, true);
    }

    /**
     * Get EditView object
     * @return EditView
     */
    protected function getEditView()
    {
        return new EditView();
    }


    /**
     * Finds and returns the best viewdefs to use:
     *  1. custom/module/quickcreatedefs.php
     *  2. module/quickcreatedefs.php
     *  3. custom/module/editviewdefs.php
     *  4. module/editviewdefs.php
     *
     * @param string $module
     * @param string $view
     * @return string The path to the viewdefs file to use
     */
    public function getModuleViewDefsSourceFile($module, $view)
    {
        $base = 'modules/' . $module . '/metadata/';
        $source = 'custom/' . $base . strtolower($view) . 'defs.php';
        if (!file_exists($source)) {
            $source = $base . strtolower($view) . 'defs.php';
            if (!file_exists($source)) {
                //if our view does not exist default to EditView
                $this->viewType = 'EditView';
                $source = 'custom/' . $base . 'editviewdefs.php';
                if (!file_exists($source)) {
                    $source = $base . 'editviewdefs.php';
                }
            }
        }

        return $source;
    }
}
