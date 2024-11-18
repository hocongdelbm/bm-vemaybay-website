<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**
 * Base Sugar view
 * @api
 */
class SugarView
{
    const NO_ERROR = 0;
    const ERR_EMPTY_SCOPE = 1;
    const ERR_EMPTY_MODULE_DIR = 2;
    const ERR_NOT_ARRAY = 3;
    const ERR_NOT_SUB_ARRAY = 4;
    const WARN_SCOPE_EXISTS = 5;

    /**
     * @var array $view_object_map
     * This array is meant to hold an objects/data that we would like to pass between
     * the controller and the view.  The bean will automatically be set for us, but this
     * is meant to hold anything else.
     */
    public $view_object_map = array();

    /**
     * @var string module
     * The name of the current module.
     */
    public $module = '';

    /**
     * @var string action
     * The name of the current action.
     */
    public $action = '';

    /**
     * @var SugarBean
     */
    public $bean;

    /**
     * @var Sugar_Smarty
     * Sugar_Smarty. This is useful if you have a view and a subview you can
     * share the same smarty object.
     */
    public $ss;

    /**
     * @var array $errors
     * Any errors that occurred this can either be set by the view or the controller or the model
     */
    public $errors = array();

    /**
     * @var boolean $suppressDisplayErrors
     * Set to true if you do not want to display errors from SugarView::displayErrors(); instead they will be returned
     */
    public $suppressDisplayErrors = false;

    /**
     * @var array $options
     * Options for what UI elements to hide/show/
     */
    public $options = array(
        'show_header' => true,
        'show_title' => true,
        'show_subpanels' => false,
        'show_search' => true,
        'show_footer' => true,
        'show_javascript' => true,
        'view_print' => false,
    );

    /**
     * @var string $type
     * Represents which view to use for loading metadata definitions eg 'edit', 'detail' or list etc.
     */
    public $type;

    /**
     * @var float $responseTime
     * Measure time it takes to process view
     */
    public $responseTime;

    /**
     * @var integer $fileResources
     *  Print out the resources used in constructing the page.
     */
    public $fileResources;

    /**
     *
     * @var array
     */
    private $settings = [];

    /**
     * SugarView constructor.
     * @deprecated since version 7.11
     */
    public function __construct()
    {
        LoggerManager::getLogger()->deprecated();
    }

    /**
     * @param SugarBean $bean
     * @param array $view_object_map
     */
    public function init(
        $bean = null,
        $view_object_map = array()
    ) {
        $this->bean = $bean;
        $this->view_object_map = $view_object_map;
        $this->action = isset($GLOBALS['action']) ? $GLOBALS['action'] : null;
        $this->module = isset($GLOBALS['module']) ? $GLOBALS['module'] : null;
        $this->_initSmarty();
    }

    /**
     * Set up smarty template
     */
    protected function _initSmarty()
    {
        $this->ss = new Sugar_Smarty();
        if (!isset($GLOBALS['mod_strings'])) {
            $GLOBALS['log']->warn('Undefined index: mod_strings');
            $GLOBALS['mod_strings'] = array();
        }
        if (!isset($GLOBALS['app_strings'])) {
            $GLOBALS['log']->warn('Undefined index: app_strings');
            $GLOBALS['app_strings'] = array();
        }
        $this->ss->assign('MOD', $GLOBALS['mod_strings']);
        $this->ss->assign('APP', $GLOBALS['app_strings']);
    }

    /**
     * This method will be called from the controller and is not meant to be overridden.
     */
    public function process()
    {
        LogicHook::initialize();
        $this->_checkModule();

        //trackView has to be here in order to track for breadcrumbs
        $this->_trackView();

        //For the ajaxUI, we need to use output buffering to return the page in an ajax friendly format
        if ($this->_getOption('json_output')) {
            ob_start();
            if (!empty($_REQUEST['ajax_load']) && !empty($_REQUEST['loadLanguageJS'])) {
                echo $this->_getModLanguageJS();
            }
        }

        if ($this->_getOption('show_header')) {
            $this->displayHeader();
        } else {
            $this->renderJavascript();
        }

        $this->_buildModuleList();
        $this->preDisplay();
        $this->displayErrors();
        $this->display();

        if (!empty($this->module)) {
            $GLOBALS['logic_hook']->call_custom_logic($this->module, 'after_ui_frame');
        } else {
            $GLOBALS['logic_hook']->call_custom_logic('', 'after_ui_frame');
        }

        // We have to update jsAlerts as soon as possible
        if (!isset($_SESSION['isMobile']) &&
            ($this instanceof ViewList || $this instanceof ViewDetail || $this instanceof ViewEdit)
        ) {
            if (isset($_SESSION['alerts_output']) && isset($_SESSION['alerts_output_timestamp']) &&
                $_SESSION['alerts_output_timestamp'] >= (date('U')-60)
            ) {
                echo $_SESSION['alerts_output'];
            } else {
                $jsAlerts = new jsAlerts();
                ob_start();
                echo $jsAlerts->getScript();
                $jsAlertsOutput = ob_get_clean();
                //save to session so we dont have to load this every time
                $_SESSION['alerts_output'] = $jsAlertsOutput;
                $_SESSION['alerts_output_timestamp'] = date('U');
                echo $jsAlertsOutput;
            }
        }

        if ($this->_getOption('show_subpanels') && !empty($_REQUEST['record'])) {
            $this->_displaySubPanels();
        }

        if ($this->action === 'Login') {
            //this is needed for a faster loading login page ie won't render unless the tables are closed
            ob_flush();
        }
        if ($this->_getOption('show_footer')) {
            $this->displayFooter();
        }
        $GLOBALS['logic_hook']->call_custom_logic('', 'after_ui_footer');
        if ($this->_getOption('json_output')) {
            $content = ob_get_clean();
            $module = $this->module;
            $ajax_ret = array(
                'content' => mb_detect_encoding($content) == "UTF-8" ? $content : utf8_encode($content),
                'menu' => array(
                    'module' => $module,
                    'label' => translate($module),
                    $this->getMenu($module),
                ),
                'title' => $this->getBrowserTitle(),
                'action' => isset($_REQUEST['action']) ? $_REQUEST['action'] : "",
                'record' => isset($_REQUEST['record']) ? $_REQUEST['record'] : "",
                'favicon' => $this->getFavicon(),
            );

            if (SugarThemeRegistry::current()->name == 'Classic' || SugarThemeRegistry::current()->classic) {
                $ajax_ret['moduleList'] = $this->displayHeader(true);
            }

            if (empty($this->responseTime)) {
                $this->_calculateFooterMetrics();
            }
            $ajax_ret['responseTime'] = $this->responseTime;
            $json = getJSONobj();
            echo $json->encode($ajax_ret);
            $GLOBALS['app']->headerDisplayed = false;
            ob_flush();
        }
        //Do not track if there is no module or if module is not a String
        $this->_track();
    }

    /**
     * This method will display the errors on the page.
     */
    public function displayErrors()
    {
        $errors = '';

        foreach ($this->errors as $error) {
            $errors .= '<span class="error">' . $error . '</span><br>';
        }

        if (!$this->suppressDisplayErrors) {
            echo $errors;

            return '';
        }

        return $errors;
    }

    /**
     * [OVERRIDE] - This method is meant to overridden in a subclass. The purpose of this method is
     * to allow a view to do some preprocessing before the display method is called. This becomes
     * useful when you have a view defined at the application level and then within a module
     * have a sub-view that extends from this application level view.  The application level
     * view can do the setup in preDisplay() that is common to itself and any subviews
     * and then the subview can just override display(). If it so desires, can also override
     * preDisplay().
     */
    public function preDisplay()
    {
    }

    /**
     * [OVERRIDE] - This method is meant to overridden in a subclass. This method
     * will handle the actual display logic of the view.
     */
    public function display()
    {
    }

    /**
     * trackView
     */
    protected function _trackView()
    {
        $action = strtolower($this->action);
        //Skip save, tracked in SugarBean instead
        if ($action == 'save') {
            return;
        }

        $trackerManager = TrackerManager::getInstance();
        $timeStamp = TimeDate::getInstance()->nowDb();
        if ($monitor = $trackerManager->getMonitor('tracker')) {
            $monitor->setValue('action', $action);
            $monitor->setValue('user_id', $GLOBALS['current_user']->id);
            $monitor->setValue('module_name', $this->module);
            $monitor->setValue('date_modified', $timeStamp);
            $monitor->setValue(
                'visible',
                (($monitor->action == 'detailview') || ($monitor->action == 'editview')) ? 1 : 0
            );

            // call fields ip_address and browser_name
            $monitor->setValue('ip_address', get_ip_address_from_client());
            $monitor->setValue('browser_name', get_browser_name($_SERVER['HTTP_SEC_CH_UA']));
    
            if (!empty($this->bean->id)) {
                $monitor->setValue('item_id', $this->bean->id);
                $monitor->setValue('item_summary', $this->bean->get_summary_text());
            }

            //If visible is true, but there is no bean, do not track (invalid/unauthorized reference)
            //Also, do not track save actions where there is no bean id
            if ($monitor->visible && empty($this->bean->id)) {
                $trackerManager->unsetMonitor($monitor);

                return;
            }
            $trackerManager->saveMonitor($monitor, true, true);
        }
    }

    /**
     * Displays the header on section of the page; basically everything before the content
     *
     * @param bool $retModTabs
     *
     * @return string
     */
    public function displayHeader($retModTabs = false)
    {
        global $theme;
        global $max_tabs;
        global $app_strings;
        global $current_user;
        global $sugar_config;
        global $app_list_strings;
        global $mod_strings;
        global $current_language;

        $GLOBALS['app']->headerDisplayed = true;

        $themeObject = SugarThemeRegistry::current();
        $theme = (string)$themeObject;

        $ss = new Sugar_Smarty();
        $ss->assign("APP", $app_strings);
        $ss->assign("THEME", $theme);
        $ss->assign("THEME_CONFIG", $themeObject->getConfig());
        $ss->assign("THEME_IE6COMPAT", $themeObject->ie6compat ? 'true' : 'false');
        $ss->assign("MODULE_NAME", $this->module);
        $ss->assign("langHeader", get_language_header());
        $ss->assign("BROWSER_TITLE", $this->getBrowserTitle());

        // AGENT STATUS - HAIHUGN
        if(isset($current_user->agent_status) && !empty($current_user->agent_status)){
            $ss->assign("AGENT_STATUS", $current_user->agent_status);
        } 

        // set ab testing if exists
        $testing = (isset($_REQUEST["testing"]) ? $_REQUEST['testing'] : "a");
        $ss->assign("ABTESTING", $testing);

        // get browser title
        $ss->assign("SYSTEM_NAME", $this->getBrowserTitle());

        // get css
        $css = $themeObject->getCSS();
        if ($this->_getOption('view_print')) {
            // $css .= '<link rel="stylesheet" type="text/css" href="' . $themeObject->getCSSURL('print.css') . '" media="all" />';
            $css .= '<link rel="stylesheet" type="text/css" href="themes/SuiteP/css/print.css" media="all" />';
        }
        $ss->assign("SUGAR_CSS", $css);

        // get javascript
        ob_start();
        $this->renderJavascript();

        $ss->assign("SUGAR_JS", ob_get_contents() . $themeObject->getJS());
        ob_end_clean();

        // get favicon
        if (isset($GLOBALS['sugar_config']['default_module_favicon'])) {
            $module_favicon = $GLOBALS['sugar_config']['default_module_favicon'];
        } else {
            $module_favicon = false;
        }

        $favicon = $this->getFavicon();
        $ss->assign('FAVICON_URL', $favicon['url']);

        // build the shortcut menu
        $shortcut_menu = array();
        foreach ($this->getMenu() as $key => $menu_item) {
            $shortcut_menu[$key] = array(
                "URL" => ajaxLink($menu_item[0]),
                "LABEL" => $menu_item[1],
                "MODULE_NAME" => $menu_item[2],
            );
        }
        $ss->assign("SHORTCUT_MENU", $shortcut_menu);

        // handle rtl text direction
        if (isset($_REQUEST['RTL']) && $_REQUEST['RTL'] == 'RTL') {
            $_SESSION['RTL'] = true;
        }
        if (isset($_REQUEST['LTR']) && $_REQUEST['LTR'] == 'LTR') {
            unset($_SESSION['RTL']);
        }
        if (isset($_SESSION['RTL']) && $_SESSION['RTL']) {
            $ss->assign("DIR", 'dir="RTL"');
        }

        // handle resizing of the company logo correctly on the fly
        $companyLogoURL = $themeObject->getImageURL('company_logo.png');
        $companyLogoURL_arr = explode('?', $companyLogoURL);
        $companyLogoURL = $companyLogoURL_arr[0];

        $company_logo_attributes = sugar_cache_retrieve('company_logo_attributes');
        if (!empty($company_logo_attributes)) {
            $ss->assign("COMPANY_LOGO_MD5", $company_logo_attributes[0]);
            $ss->assign("COMPANY_LOGO_WIDTH", $company_logo_attributes[1]);
            $ss->assign("COMPANY_LOGO_HEIGHT", $company_logo_attributes[2]);
        } else {
            // Always need to md5 the file
            $ss->assign("COMPANY_LOGO_MD5", md5_file($companyLogoURL));

            list($width, $height) = getimagesize($companyLogoURL);
            if ($width > 212 || $height > 40) {
                $resizePctWidth = ($width - 212) / 212;
                $resizePctHeight = ($height - 40) / 40;
                if ($resizePctWidth > $resizePctHeight) {
                    $resizeAmount = $width / 212;
                } else {
                    $resizeAmount = $height / 40;
                }
                $ss->assign("COMPANY_LOGO_WIDTH", round($width * (1 / $resizeAmount)));
                $ss->assign("COMPANY_LOGO_HEIGHT", round($height * (1 / $resizeAmount)));
            } else {
                $ss->assign("COMPANY_LOGO_WIDTH", $width);
                $ss->assign("COMPANY_LOGO_HEIGHT", $height);
            }

            // Let's cache the results
            sugar_cache_put(
                'company_logo_attributes',
                array(
                    $ss->get_template_vars("COMPANY_LOGO_MD5"),
                    $ss->get_template_vars("COMPANY_LOGO_WIDTH"),
                    $ss->get_template_vars("COMPANY_LOGO_HEIGHT")
                )
            );
        }
        $ss->assign(
            "COMPANY_LOGO_URL",
            getJSPath($companyLogoURL) . "&logo_md5=" . $ss->get_template_vars("COMPANY_LOGO_MD5")
        );

        // get the global links
        $gcls = array();
        $global_control_links = array();
        require("include/globalControlLinks.php");

        foreach ($global_control_links as $key => $value) {
            if ($key == 'users') {   //represents logout link.
                $ss->assign("LOGOUT_LINK", $value['linkinfo'][key($value['linkinfo'])]);
                $ss->assign("LOGOUT_LABEL", key($value['linkinfo']));//key value for first element.
                continue;
            }

            foreach ($value as $linkattribute => $attributevalue) {
                // get the main link info
                if ($linkattribute == 'linkinfo') {
                    $gcls[$key] = array(
                        "LABEL" => key($attributevalue),
                        "URL" => current($attributevalue),
                        "SUBMENU" => array(),
                    );

                    if (substr($gcls[$key]["URL"], 0, 11) == "javascript:") {
                        $gcls[$key]["ONCLICK"] = substr($gcls[$key]["URL"], 11);
                        $gcls[$key]["URL"] = "javascript:void(0)";
                    }

                    if (isset($attributevalue['target'])) {
                        $gcls[$key]["TARGET"] = $attributevalue['target'];
                    }
                }
                // and now the sublinks
                if ($linkattribute === 'submenu' && is_array($attributevalue)) {
                    foreach ($attributevalue as $submenulinkkey => $submenulinkinfo) {
                        $gcls[$key]['SUBMENU'][$submenulinkkey] = array(
                            "LABEL" => key($submenulinkinfo),
                            "URL" => current($submenulinkinfo),
                        );
                    }
                    if (substr($gcls[$key]['SUBMENU'][$submenulinkkey]["URL"], 0, 11) === "javascript:") {
                        $gcls[$key]['SUBMENU'][$submenulinkkey]["ONCLICK"] =
                            substr($gcls[$key]['SUBMENU'][$submenulinkkey]["URL"], 11);
                        $gcls[$key]['SUBMENU'][$submenulinkkey]["URL"] = "javascript:void(0)";
                    }
                }
            }
        }
        $ss->assign("GCLS", $gcls);

        $ss->assign("SEARCH", isset($_REQUEST['query_string']) ? $_REQUEST['query_string'] : '');

        if ($this->action == "EditView" || $this->action == "Login") {
            $ss->assign("ONLOAD", 'onload="set_focus()"');
        }

        $ss->assign("AUTHENTICATED", isset($_SESSION["authenticated_user_id"]));

        // get other things needed for page style popup
        if (isset($_SESSION["authenticated_user_id"])) {
            // get the current user name and id
            $ss->assign(
                "CURRENT_USER",
                $current_user->full_name == '' || !showFullName() ? $current_user->user_name : $current_user->full_name
            );
            $ss->assign("CURRENT_USER_ID", $current_user->id);

            if (!empty($current_user->photo)) {
                $photo_profile = '<img src="index.php?entryPoint=download&id=' . $current_user->id . '_photo&type=Users" alt="photo profile">';
                $ss->assign("CURRENT_USER_PHOTO", $photo_profile);
            } else {
                $photo_profile = '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="22" height="22" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"></path></svg>';
                $ss->assign("CURRENT_USER_PHOTO", $photo_profile);
            }
            
            // get the last viewed records
            $favorites = BeanFactory::getBean('Favorites');
            $favorite_records = $favorites->getCurrentUserSidebarFavorites();
            $ss->assign("favoriteRecords", $favorite_records);

            $tracker = BeanFactory::getBean('Trackers');
            $history = $tracker->get_recently_viewed($current_user->id);
            $ss->assign("recentRecords", $this->processRecentRecords($history));
        }

        $bakModStrings = $mod_strings;
        if (isset($_SESSION["authenticated_user_id"])) {
            // get the module list
            $moduleTopMenu = array();

            $max_tabs = $current_user->getPreference('max_tabs');
            // Attempt to correct if max tabs count is extremely high.
            if (!isset($max_tabs) || $max_tabs <= 0 || $max_tabs > 10) {
                $max_tabs = $GLOBALS['sugar_config']['default_max_tabs'];
                $current_user->setPreference('max_tabs', $max_tabs, 0, 'global');
            }

            $moduleTab = $this->_getModuleTab();
            $ss->assign('MODULE_TAB', $moduleTab);

            // See if they are using grouped tabs or not (removed in 6.0, returned in 6.1)
            $user_navigation_paradigm = $current_user->getPreference('navigation_paradigm');
            if (!isset($user_navigation_paradigm)) {
                $user_navigation_paradigm = $GLOBALS['sugar_config']['default_navigation_paradigm'];
            }

            // Get the full module list for later use
            foreach (query_module_access_list($current_user) as $module) {
                // Bug 25948 - Check for the module being in the moduleList
                if (isset($app_list_strings['moduleList'][$module])) {
                    $fullModuleList[$module] = $app_list_strings['moduleList'][$module];
                }
            }

            if (!should_hide_iframes()) {
                $iFrame = new iFrame();
                $frames = $iFrame->lookup_frames('tab');
                foreach ($frames as $key => $values) {
                    $fullModuleList[$key] = $values;
                }
            } elseif (isset($fullModuleList['iFrames'])) {
                unset($fullModuleList['iFrames']);
            }

            if ($user_navigation_paradigm == 'gm' && isset($themeObject->group_tabs) && $themeObject->group_tabs) {
                // We are using grouped tabs
                require_once('include/GroupedTabs/GroupedTabStructure.php');
                $groupedTabsClass = new GroupedTabStructure();
                $modules = query_module_access_list($current_user);

                //handle with submoremodules
                $max_tabs = $current_user->getPreference('max_tabs');
                // If the max_tabs isn't set incorrectly, set it within the range, to the default max sub tabs size
                if (!isset($max_tabs) || $max_tabs <= 0 || $max_tabs > 10) {
                    // We have a default value. Use it
                    if (isset($GLOBALS['sugar_config']['default_max_tabs'])) {
                        $max_tabs = $GLOBALS['sugar_config']['default_max_tabs'];
                    } else {
                        $max_tabs = 8;
                    }
                }

                $subMoreModules = false;
                $groupTabs = $groupedTabsClass->get_tab_structure(get_val_array($modules));
                // We need to put this here, so the "All" group is valid for the user's preference.
                $groupTabs[$app_strings['LBL_TABGROUP_ALL']]['modules'] = $fullModuleList;

                // Setup the default group tab.
                $allGroup = $app_strings['LBL_TABGROUP_ALL'];
                $ss->assign('currentGroupTab', $allGroup);
                $currentGroupTab = $allGroup;
                $usersGroup = $current_user->getPreference('theme_current_group');
                // Figure out which tab they currently have selected (stored as a user preference)
                if (!empty($usersGroup) && isset($groupTabs[$usersGroup])) {
                    $currentGroupTab = $usersGroup;
                } else {
                    $current_user->setPreference('theme_current_group', $currentGroupTab);
                }

                $ss->assign('currentGroupTab', $currentGroupTab);
                $usingGroupTabs = true;
            } else {
                // Setup the default group tab.
                $ss->assign('currentGroupTab', $app_strings['LBL_TABGROUP_ALL']);

                $usingGroupTabs = false;
                $groupTabs[$app_strings['LBL_TABGROUP_ALL']]['modules'] = $fullModuleList;
            }

            $topTabList = array();

            // Now time to go through each of the tab sets and fix them up.
            foreach ($groupTabs as $tabIdx => $tabData) {
                $topTabs = $tabData['modules'];

                // Sort the list of modules alphabetically
                if ($current_user->getPreference('sort_modules_by_name')) {
                    asort($topTabs);
                }

                // put the current module at the top of the list
                if (!empty($moduleTab) && isset($tabData['modules'][$moduleTab])) {
                    unset($topTabs[$moduleTab]);
                    $topTabs = array_merge(
                        array($moduleTab => $tabData['modules'][$moduleTab]),
                        $topTabs
                    );
                }

                if (!is_array($topTabs)) {
                    $topTabs = array();
                }
                $extraTabs = array();

                // Split it in to the tabs that go across the top, and the ones that are on the extra menu.
                if (count($topTabs) > $max_tabs) {
                    $extraTabs = array_splice($topTabs, $max_tabs);
                }
                // Make sure the current module is accessable through one of the top tabs
                if (!isset($topTabs[$moduleTab])) {
                    // Nope, we need to add it.
                    // First, take it out of the extra menu, if it's there
                    if (isset($extraTabs[$moduleTab])) {
                        unset($extraTabs[$moduleTab]);
                    }
                    if (count($topTabs) >= $max_tabs - 1) {
                        // We already have the maximum number of tabs, so we need to shuffle the last one
                        // from the top to the first one of the extras
                        $lastElem = array_splice($topTabs, $max_tabs - 1);
                        $extraTabs = $lastElem + $extraTabs;
                    }
                    if (!empty($moduleTab)) {
                        $topTabs[$moduleTab] = $app_list_strings['moduleList'][$moduleTab];
                        if (count($topTabs) >= $max_tabs - 1) {
                            $extraTabs[$moduleTab] = $app_list_strings['moduleList'][$moduleTab];
                        }
                    }
                }

                // Get a unique list of the top tabs so we can build the popup menus for them
                foreach ($topTabs as $moduleKey => $module) {
                    $topTabList[$moduleKey] = $module;
                }

                $groupTabs[$tabIdx]['modules'] = $topTabs;
                $groupTabs[$tabIdx]['extra'] = $extraTabs;
            }

            foreach ($groupTabs as $key => $tabGroup) {
                if (count($topTabs) >= $max_tabs - 1 && $key !== $app_strings['LBL_TABGROUP_ALL'] && in_array(
                    $tabGroup['modules'][$moduleTab],
                    $tabGroup['extra']
                )
                ) {
                    unset($groupTabs[$key]['modules'][$moduleTab]);
                }
            }
        }

        if (isset($topTabList) && is_array($topTabList)) {
            // Adding shortcuts array to menu array for displaying shortcuts associated with each module
            $shortcutTopMenu = array();
            foreach ($topTabList as $module_key => $label) {
                global $mod_strings;
                $mod_strings = return_module_language($current_language, $module_key);
                foreach ($this->getMenu($module_key) as $key => $menu_item) {
                    $shortcutTopMenu[$module_key][$key] = array(
                        "URL" => ajaxLink($menu_item[0]),
                        "LABEL" => $menu_item[1],
                        "MODULE_NAME" => $menu_item[2],
                        "ID" => $menu_item[2] . "_link",
                    );
                }
            }
            if (!empty($sugar_config['lock_homepage']) && $sugar_config['lock_homepage'] == true) {
                $ss->assign('lock_homepage', true);
            }

            $ss->assign("groupTabs", $groupTabs);
            $ss->assign("shortcutTopMenu", $shortcutTopMenu);
            $ss->assign('USE_GROUP_TABS', $usingGroupTabs);

            // This is here for backwards compatibility, someday, somewhere, it will be able to be removed
            $ss->assign("moduleTopMenu", $groupTabs[$app_strings['LBL_TABGROUP_ALL']]['modules']);
            $ss->assign("moduleExtraMenu", $groupTabs[$app_strings['LBL_TABGROUP_ALL']]['extra']);
        }

        if (isset($extraTabs) && is_array($extraTabs)) {
            // Adding shortcuts array to extra menu array for displaying shortcuts associated with each module
            $shortcutExtraMenu = array();
            foreach ($extraTabs as $module_key => $label) {
                global $mod_strings;
                $mod_strings = return_module_language($current_language, $module_key);
                foreach ($this->getMenu($module_key) as $key => $menu_item) {
                    $shortcutExtraMenu[$module_key][$key] = array(
                        "URL" => ajaxLink($menu_item[0]),
                        "LABEL" => $menu_item[1],
                        "MODULE_NAME" => $menu_item[2],
                        "ID" => $menu_item[2] . "_link",
                    );
                }
            }
            $ss->assign("shortcutExtraMenu", $shortcutExtraMenu);
        }

        if (!empty($current_user)) {
            $ss->assign("max_tabs", $current_user->getPreference("max_tabs"));
        }

        global $mod_strings;
        $mod_strings = $bakModStrings;
        $headerTpl = $themeObject->getTemplate('header.tpl');
        if (inDeveloperMode()) {
            $ss->clear_compiled_tpl($headerTpl);
        }

        if ($retModTabs) {
            return $ss->fetch($themeObject->getTemplate('_headerModuleList.tpl'));
        } else {
            $ss->display($headerTpl);

            $this->includeClassicFile('modules/Administration/DisplayWarnings.php');

            $messages = SugarApplication::getErrorMessages();
            if (!empty($messages)) {
                foreach ($messages as $message) {
                    echo '<p class="error">' . $message . '</p>';
                }
            }

            $messages = SugarApplication::getSuccessMessages();
            if (!empty($messages)) {
                foreach ($messages as $message) {
                    echo '<p class="success">' . $message . '</p>';
                }
            }
        }
    }

    public function getModuleMenuHTML()
    {
    }

    /**
     * If the view is classic then this method will include the file and
     * setup any global variables.
     *
     * @param string $file
     */
    public function includeClassicFile(
        $file
    ) {
        global $sugar_config, $theme, $current_user, $sugar_version, $sugar_flavor, $mod_strings, $app_strings, $app_list_strings, $action;
        global $gridline, $request_string, $modListHeader, $dashletData, $authController, $locale, $currentModule, $import_bean_map, $image_path, $license;
        global $user_unique_key, $server_unique_key, $barChartColors, $modules_exempt_from_availability_check, $dictionary, $current_language, $beanList, $beanFiles, $sugar_build, $sugar_codename;
        global $timedate, $login_error; // cn: bug 13855 - timedate not available to classic views.
        if (!empty($this->module)) {
            $currentModule = $this->module;
        }
        include_once($file);
    }

    protected function _displayLoginJS()
    {
        global $sugar_config, $timedate;

        $template = new Sugar_Smarty();

        if (isset($this->bean->module_dir)) {
            $template->assign('MODULE_SUGAR_GRP1', $this->bean->module_dir);
        }
        if (isset($_REQUEST['action'])) {
            $template->assign('ACTION_SUGAR_GRP1', $_REQUEST['action']);
        }

        echo '<script>jscal_today = 1000*' .
            $timedate->asUserTs($timedate->getNow()) .
            '; if(typeof app_strings == "undefined") app_strings = new Array();</script>';
        if (!is_file(sugar_cached("include/javascript/sugar_grp1.js"))) {
            $_REQUEST['root_directory'] = ".";
            require_once("jssource/minify_utils.php");
            ConcatenateFiles(".");
        }
        $template->assign('SUGAR_GRP1_JQUERY', getVersionedPath('cache/include/javascript/sugar_grp1_jquery.js'));
        $template->assign('SUGAR_GRP1_YUI', getVersionedPath('cache/include/javascript/sugar_grp1_yui.js'));
        $template->assign('SUGAR_GRP1', getVersionedPath('cache/include/javascript/sugar_grp1.js'));
        $template->assign('CALENDAR', getVersionedPath('include/javascript/calendar.js'));

        echo $template->fetch('include/MVC/View/tpls/displayLoginJS.tpl');
    }

    /**
     * Get JS validation code for views
     */
    public static function getJavascriptValidation()
    {
        global $timedate;
        $cal_date_format = $timedate->get_cal_date_format();
        $timereg = $timedate->get_regular_expression($timedate->get_time_format());
        $datereg = $timedate->get_regular_expression($timedate->get_date_format());
        $date_pos = '';
        foreach ($datereg['positions'] as $type => $pos) {
            if (empty($date_pos)) {
                $date_pos .= "'$type': $pos";
            } else {
                $date_pos .= ",'$type': $pos";
            }
        }

        $time_separator = $timedate->timeSeparator();
        $hour_offset = $timedate->getUserUTCOffset() * 60;

        // Add in the number formatting styles here as well, we have been handling this with individual modules.
        require_once('modules/Currencies/Currency.php');
        list($num_grp_sep, $dec_sep) = get_number_separators();

        $the_script =
            "<script type=\"text/javascript\">\n" .
            "\tvar time_reg_format = '" .
            $timereg['format'] .
            "';\n" .
            "\tvar date_reg_format = '" .
            $datereg['format'] .
            "';\n" .
            "\tvar date_reg_positions = { $date_pos };\n" .
            "\tvar time_separator = '$time_separator';\n" .
            "\tvar cal_date_format = '$cal_date_format';\n" .
            "\tvar time_offset = $hour_offset;\n" .
            "\tvar num_grp_sep = '$num_grp_sep';\n" .
            "\tvar dec_sep = '$dec_sep';\n" .
            "</script>";

        return $the_script;
    }

    /**
     * Called from process(). This method will display the correct javascript.
     */
    protected function _displayJavascript()
    {
        global $locale, $sugar_config, $timedate;

        if ($this->_getOption('show_javascript')) {
            if (!$this->_getOption('show_header')) {
                $langHeader = get_language_header();

                echo <<<EOHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html {$langHeader}>
<head>
EOHTML;
            }

            $js_vars = array(
                "sugar_cache_dir" => "cache/",
            );

            if (isset($this->bean->module_dir)) {
                $js_vars['module_sugar_grp1'] = $this->bean->module_dir;
            }
            if (isset($_REQUEST['action'])) {
                $js_vars['action_sugar_grp1'] = $_REQUEST['action'];
            }
            echo '<script>jscal_today = 1000*' .
                $timedate->asUserTs($timedate->getNow()) .
                '; if(typeof app_strings == "undefined") app_strings = new Array();</script>';
            if (!is_file(sugar_cached("include/javascript/sugar_grp1.js")) ||
                !is_file(sugar_cached("include/javascript/sugar_grp1_yui.js")) ||
                !is_file(sugar_cached("include/javascript/sugar_grp1_jquery.js"))
            ) {
                $_REQUEST['root_directory'] = ".";
                require_once("jssource/minify_utils.php");
                ConcatenateFiles(".");
            }
            echo getVersionedScript('cache/include/javascript/sugar_grp1_jquery.js');
            echo getVersionedScript('cache/include/javascript/sugar_grp1_yui.js');
            echo getVersionedScript('cache/include/javascript/sugar_grp1.js');
            echo getVersionedScript('include/javascript/calendar.js');

            // output necessary config js in the top of the page
            $config_js = $this->getSugarConfigJS();
            if (!empty($config_js)) {
                echo "<script>\n" . implode("\n", $config_js) . "</script>\n";
            }

            if ($this->hasDomJS()) {
                echo "
                        <script type='text/javascript'>
                        SUGAR.append(SUGAR, { settings:".$this->getDomJS()." } );
                        </script>
                        ";
            }

            if (isset($sugar_config['email_sugarclient_listviewmaxselect'])) {
                echo "<script>SUGAR.config.email_sugarclient_listviewmaxselect = {$GLOBALS['sugar_config']['email_sugarclient_listviewmaxselect']};</script>";
            }

            $image_server = (defined('TEMPLATE_URL')) ? TEMPLATE_URL . '/' : '';
            echo '<script type="text/javascript">SUGAR.themes.image_server="' .
                $image_server .
                '";</script>'; // cn: bug 12274 - create session-stored key to defend against CSRF
            echo '<script type="text/javascript">var name_format = "' . $locale->getLocaleFormatMacro() . '";</script>';
            echo self::getJavascriptValidation();
            if (!is_file(sugar_cached('jsLanguage/') . $GLOBALS['current_language'] . '.js')) {
                require_once('include/language/jsLanguage.php');
                jsLanguage::createAppStringsCache($GLOBALS['current_language']);
            }
            echo getVersionedScript(
                'cache/jsLanguage/' . $GLOBALS['current_language'] . '.js',
                $GLOBALS['sugar_config']['js_lang_version']
            );

            echo $this->_getModLanguageJS();

            //echo out the $js_vars variables as javascript variables
            echo "<script type='text/javascript'>\n";
            foreach ($js_vars as $var => $value) {
                echo "var {$var} = '{$value}';\n";
            }
            echo "</script>\n";
        }
    }

    /**
     * @return string
     */
    protected function _getModLanguageJS()
    {
        if (!is_file(sugar_cached('jsLanguage/') . $this->module . '/' . $GLOBALS['current_language'] . '.js')) {
            require_once('include/language/jsLanguage.php');
            jsLanguage::createModuleStringsCache($this->module, $GLOBALS['current_language']);
        }

        return getVersionedScript(
            "cache/jsLanguage/{$this->module}/" . $GLOBALS['current_language'] . '.js',
            $GLOBALS['sugar_config']['js_lang_version']
        );
    }

    /**
     * Called from process(). This method will display the footer on the page.
     */
    public function displayFooter()
    {
        if (empty($this->responseTime)) {
            $this->_calculateFooterMetrics();
        }
        global $app_strings, $sugar_config;
        $server_unique_key = isset($sugar_config['unique_key']) ? $sugar_config['unique_key'] : '';
        $themeObject = SugarThemeRegistry::current();

        $ss = new Sugar_Smarty();
        $ss->assign("AUTHENTICATED", isset($_SESSION["authenticated_user_id"]));
        $ss->assign('MOD', return_module_language($GLOBALS['current_language'], 'Users'));

        $bottomLinkList = array();
        if (isset($this->action) && $this->action != "EditView") {
            $bottomLinkList['print'] = array($app_strings['LNK_PRINT'] => getPrintLink());
        }
        $bottomLinkList['backtotop'] = array($app_strings['LNK_BACKTOTOP'] => 'javascript:SUGAR.util.top();');

        $bottomLinksStr = "";
        foreach ($bottomLinkList as $key => $value) {
            foreach ($value as $text => $link) {
                $href = $link;
                if (substr($link, 0, 11) == "javascript:") {
                    $onclick = " onclick=\"" . substr($link, 11) . "\"";
                    $href = "javascript:void(0)";
                } else {
                    $onclick = "";
                }
                $imageURL = SugarThemeRegistry::current()->getImageURL($key . '.gif');
                $bottomLinksStr .= "<a href=\"{$href}\"";
                $bottomLinksStr .= (isset($onclick)) ? $onclick : "";
                $bottomLinksStr .= "><img src='{$imageURL}' alt=''>"; //keeping alt blank(text will be read instead)
                $bottomLinksStr .= " " . $text . "</a>";
            }
        }
        $ss->assign("BOTTOMLINKS", $bottomLinksStr);
        if (SugarConfig::getInstance()->get('calculate_response_time', false)) {
            $ss->assign('STATISTICS', $this->_getStatistics());
        }

        // Under the License referenced above, you are required to leave in all copyright statements in both
        // the code and end-user application.

        $copyright =
            '&copy; 2004-2013 SugarCRM Inc. The Program is provided AS IS, without warranty.  Licensed under <a href="LICENSE.txt" target="_blank" class="copyRightLink">AGPLv3</a>.<br>This program is free software; you can redistribute it and/or modify it under the terms of the <br><a href="LICENSE.txt" target="_blank" class="copyRightLink"> GNU Affero General Public License version 3</a> as published by the Free Software Foundation, including the additional permission set forth in the source code header.<br>';

        // The interactive user interfaces in modified source and object code
        // versions of this program must display Appropriate Legal Notices, as
        // required under Section 5 of the GNU General Public License version
        // 3. In accordance with Section 7(b) of the GNU General Public License
        // version 3, these Appropriate Legal Notices must retain the display
        // of the "Powered by SugarCRM" logo. If the display of the logo is
        // not reasonably feasible for technical reasons, the Appropriate
        // Legal Notices must display the words "Powered by SugarCRM".
        $attribLinkImg =
            "<img style='margin-top: 2px' border='0' width='120' height='34' src='include/images/poweredby_sugarcrm_65.png' alt='Powered By SugarCRM'>\n";

        // handle resizing of the company logo correctly on the fly
        $companyLogoURL = $themeObject->getImageURL('company_logo.png');
        $companyLogoURL_arr = explode('?', $companyLogoURL);
        $companyLogoURL = $companyLogoURL_arr[0];

        $company_logo_attributes = sugar_cache_retrieve('company_logo_attributes');
        if (!empty($company_logo_attributes)) {
            $ss->assign("COMPANY_LOGO_MD5", $company_logo_attributes[0]);
            $ss->assign("COMPANY_LOGO_WIDTH", $company_logo_attributes[1]);
            $ss->assign("COMPANY_LOGO_HEIGHT", $company_logo_attributes[2]);
        } else {
            // Always need to md5 the file
            $ss->assign("COMPANY_LOGO_MD5", md5_file($companyLogoURL));

            list($width, $height) = getimagesize($companyLogoURL);
            if ($width > 212 || $height > 40) {
                $resizePctWidth = ($width - 212) / 212;
                $resizePctHeight = ($height - 40) / 40;
                if ($resizePctWidth > $resizePctHeight) {
                    $resizeAmount = $width / 212;
                } else {
                    $resizeAmount = $height / 40;
                }
                $ss->assign("COMPANY_LOGO_WIDTH", round($width * (1 / $resizeAmount)));
                $ss->assign("COMPANY_LOGO_HEIGHT", round($height * (1 / $resizeAmount)));
            } else {
                $ss->assign("COMPANY_LOGO_WIDTH", $width);
                $ss->assign("COMPANY_LOGO_HEIGHT", $height);
            }

            // Let's cache the results
            sugar_cache_put(
                'company_logo_attributes',
                array(
                    $ss->get_template_vars("COMPANY_LOGO_MD5"),
                    $ss->get_template_vars("COMPANY_LOGO_WIDTH"),
                    $ss->get_template_vars("COMPANY_LOGO_HEIGHT")
                )
            );
        }
        $ss->assign(
            "COMPANY_LOGO_URL",
            getJSPath($companyLogoURL) . "&logo_md5=" . $ss->get_template_vars("COMPANY_LOGO_MD5")
        );

        // Bug 38594 - Add in Trademark wording
        $copyright .= 'SugarCRM is a trademark of SugarCRM, Inc. ' .
            'All other company and product names may be ' .
            'trademarks of the respective companies with which they are associated.<br />';

        if (file_exists('include/images/poweredby_sugarcrm_65.png')) {
            $copyright .= $attribLinkImg;
        }
        // End Required Image
        $ss->assign('COPYRIGHT', $copyright);

        // here we allocate the help link data
        $help_actions_blacklist = array('Login'); // we don't want to show a context help link here
        if (!in_array($this->action, $help_actions_blacklist)) {
            if (!isset($server_unique_key)) {
                LoggerManager::getLogger()->warn('Undefined index: server_unique_key');
            }
            $url =
                'javascript:void(window.open(\'index.php?module=Administration&action=SupportPortal&view=documentation' .
                '&version=' .
                $GLOBALS['sugar_version'] .
                '&edition=' .
                $GLOBALS['sugar_flavor'] .
                '&lang=' .
                $GLOBALS['current_language'] .
                '&help_module=' .
                $this->module .
                '&help_action=' .
                $this->action .
                '&key=' .
                (isset($server_unique_key) ? $server_unique_key : null) .
                '\'))';
            $label =
                (isset($GLOBALS['app_list_strings']['moduleList'][$this->module]) ?
                    $GLOBALS['app_list_strings']['moduleList'][$this->module] : $this->module) .
                ' ' .
                $app_strings['LNK_HELP'];
            $ss->assign(
                'HELP_LINK',
                SugarThemeRegistry::current()->getLink(
                    $url,
                    $label,
                    "id='help_link_two'",
                    'help-dashlet.png',
                    'class="icon"',
                    null,
                    null,
                    '',
                    'left'
                )
            );
        }
        // end

        $this->initJSSIP();

        $ss->display(SugarThemeRegistry::current()->getTemplate('footer.tpl'));
    }

    // Init jssip data (Made by DucPham 21/11/2023)
    public function initJSSIP() {
        global $current_user;
        $arr_sip_number = custom_get_sip_number();

        $html = '';
        $css = $js = '';

        if(isset($arr_sip_number[$current_user->id])) {
            $html .= '<input type="hidden" name="sip_user" id="sip_user" value="'.$arr_sip_number[$current_user->id]['user'].'" disabled />';
            $html .= '<input type="hidden" name="sip_password" id="sip_password" value="'.$arr_sip_number[$current_user->id]['password'].'" disabled />';
            $html .= '<input type="hidden" name="agent_status" id="agent_status" value="'.$current_user->agent_status.'" disabled />';
            $html .= '<input type="hidden" name="sip_instance_id" id="sip_instance_id" value="'.$current_user->id.'" disabled />';
            $html .= '
                <div id="call-overlay"></div>
                <div id="toast-incoming"></div>
                <div id="popup__voiceip--wrap" class="voiceip-wrap">
                    <div id="popup-voiceip" call_id="">
                        <i>Speaker</i>
                        <b>Camera</b>
                        <div class="voiceip-header">
                            <div class="voiceip-header__title">Cuộc gọi</div>
                        </div>
                        <div class="voiceip-body">
                            <div class="voiceip-calling">
                                <div class="skype"></div>
                            </div>
                            <div id="voiceip-timer" class="voiceip-timer">00:00:00</div>
                            <div class="voiceip-content__client">
                                <div class="wrap-info-voiceip mt-2">
                                    <p id="voiceip-info-name" style="font-size:18px; font-weight:500;"></p>
                                    <p id="voiceip-info-phone" style="font-size:18px;"></p>
                                    <p><span></span><a id="voiceip-info-zaloid" href="#" target="_blank" style="font-style: italic;"></a></p>
                                </div>
                                <div class="wrap-form-voiceip p-2" style="display:none">
                                    <div class="voiceip-group voiceip-name">
                                        <div class="voiceip-label">
                                            <svg width="26px" height="26px" stroke-width="1.65" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#9c9fa6">
                                                <path d="M5 20V19C5 15.134 8.13401 12 12 12V12C15.866 12 19 15.134 19 19V20" stroke="#9c9fa6" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z" stroke="#9c9fa6" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>
                                        <input name="voiceip-name" id="voiceip-name" type="text" value="" placeholder="Họ tên" />
                                        <input name="voiceip-contact-id" type="hidden" value="" />
                                    </div>
                                    <div class="voiceip-group voiceip-phone">
                                        <div class="voiceip-label">
                                            <svg width="20px" height="20px" stroke-width="2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#9c9fa6">
                                                <path d="M18.1182 14.702L14 15.5C11.2183 14.1038 9.5 12.5 8.5 10L9.26995 5.8699L7.81452 2L4.0636 2C2.93605 2 2.04814 2.93178 2.21654 4.04668C2.63695 6.83 3.87653 11.8765 7.5 15.5C11.3052 19.3052 16.7857 20.9564 19.802 21.6127C20.9668 21.8662 22 20.9575 22 19.7655L22 16.1812L18.1182 14.702Z" stroke="#9c9fa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>
                                        <input name="voiceip-phone" id="voiceip-phone" type="text" value="" placeholder="Điện thoại" />
                                    </div>
                                    <div class="voiceip-group voiceip-zaloid">
                                        <div class="voiceip-label">
                                            <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.65" fill="none" xmlns="http://www.w3.org/2000/svg" color="#9c9fa6">
                                                <path d="M21 3.6V20.4C21 20.7314 20.7314 21 20.4 21H3.6C3.26863 21 3 20.7314 3 20.4V3.6C3 3.26863 3.26863 3 3.6 3H20.4C20.7314 3 21 3.26863 21 3.6Z" stroke="#9c9fa6" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M10 8H14L10 16H14" stroke="#9c9fa6" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>
                                        <input name="voiceip-zalo-id" id="voiceip-zalo-id" type="text" value="" placeholder="Zalo ID" readonly/>
                                    </div>
                                    <div class="voiceip-group voiceip-email">
                                        <div class="voiceip-label">
                                            <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#9c9fa6">
                                                <path d="M7 9L12 12.5L17 9" stroke="#9c9fa6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M2 17V7C2 5.89543 2.89543 5 4 5H20C21.1046 5 22 5.89543 22 7V17C22 18.1046 21.1046 19 20 19H4C2.89543 19 2 18.1046 2 17Z" stroke="#9c9fa6" stroke-width="1.5"></path>
                                            </svg>
                                        </div>
                                        <input name="voiceip-email" id="voiceip-email" type="text" value="" placeholder="Email" />
                                    </div>
                                    <div class="voiceip-group voiceip-notes">
                                        <textarea name="voiceip-notes" id="voiceip-notes" placeholder="Ghi chú"></textarea>
                                    </div>
                                    <div class="flex-between voiceip-more my-2 rounded text-nowrap">
                                        <div class="voiceip-more__item is-success">
                                            <label for="is_success" class="me-2">Hoàn tất?</label>
                                            <input type="checkbox" class="bg-white" id="is_success"">
                                        </div>
                                        <div class="voiceip-more__item flex-start template-notes text-nowrap">
                                            <label for="template-notes">Phân loại</label>
                                            <select name="template-notes" class="box-select flex-fill w-100" id="template-notes"></select>
                                        </div> 
                                    </div>
                                    <div class="voiceip-modal-transfer" role="alert">
                                        <div class="transfer-container">
                                            <div class="transfer-header">
                                                <h4 class="transfer-title">Nhập số SIP để chuyển tiếp cuộc gọi</h4>
                                            </div>
                                            <div class="transfer-body">
                                                <input type="text" onkeyup="onSearch()" name="transfer-phone" class="load-data__transfer" id="transfer-phone" autocomplete="off"  placeholder="">
                                                <a class="btn btn-primary" id="transfer-submit">Chuyển</a>
                                                <ul id="offer-transfer-list"></ul>
                                            </div>
                                        </div>  
                                    </div>
                                </div>
                            </div>
                            <div class="calc-dtmf__wrap">
                                <div id="calc-dtmf">
                                    <div class="item" id="display_dtmf"></div>
                                    <div class="item" id="calc-layout">
                                        <div id="numPad" class="calc-section">
                                            <div class="calc-button calc-number" dtmf="7" id="seven" style="grid-area: seven;">7</div>
                                            <div class="calc-button calc-number" dtmf="8" id="eight" style="grid-area: eight;">8</div>
                                            <div class="calc-button calc-number" dtmf="9" id="nine" style="grid-area: nine;">9</div>
                                            <div class="calc-button calc-number" dtmf="4" id="four" style="grid-area: four;">4</div>
                                            <div class="calc-button calc-number" dtmf="5" id="five" style="grid-area: five;">5</div>
                                            <div class="calc-button calc-number" dtmf="6" id="six" style="grid-area: six;">6</div>
                                            <div class="calc-button calc-number" dtmf="1" id="one" style="grid-area: one;">1</div>
                                            <div class="calc-button calc-number" dtmf="2" id="two" style="grid-area: two;">2</div>
                                            <div class="calc-button calc-number" dtmf="3" id="three" style="grid-area: three;">3</div>
                                            <div class="calc-button" id="clear_dtmf" style="grid-area: clear_dtmf;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" class="bi bi-backspace" viewBox="0 0 16 16">
                                                    <path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z"></path>
                                                    <path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"></path>
                                                </svg>
                                            </div>
                                            <div class="calc-button calc-number" dtmf="0" id="zero" style="grid-area: zero;">0</div>
                                            <div class="calc-button calc-number" dtmf="*" id="submit_dtmf" style="grid-area: submit_dtmf;">*</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="voiceip-footer p-2 border-top bg-light">
                            <div class="voiceip-action">
                                <div class="voiceip-button voiceip-end">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                    </svg>
                                    <div class="voiceip-button__desc voiceip-button__desc--end">
                                        Kết thúc
                                    </div>
                                </div>
                                <div class="voiceip-button voiceip-decline">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                    </svg>
                                    <div class="voiceip-button__desc voiceip-button__desc--decline">
                                        Từ chối
                                    </div>
                                </div>
                                <div class="voiceip-button voiceip-accept">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                    </svg>
                                    <div class="voiceip-button__desc voiceip-button__desc--accept">
                                        Trả lời
                                    </div>
                                </div>
                                <div class="voiceip-button voiceip-update" booking_id="" booking_name="" type_call_booking="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                        <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                    </svg>
                                    <div class="voiceip-button__desc voiceip-button__desc--update">
                                        Cập nhật
                                    </div>
                                </div>
                                <div class="voiceip-button voiceip-viewbooking">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-stopwatch-fill" viewBox="0 0 16 16">
                                        <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07A7.001 7.001 0 0 0 8 16a7 7 0 0 0 5.29-11.584l.013-.012.354-.354.353.354a.5.5 0 1 0 .707-.707l-1.414-1.415a.5.5 0 1 0-.707.707l.354.354-.354.354-.012.012A6.97 6.97 0 0 0 9 2.071V1h.5a.5.5 0 0 0 0-1zm2 5.6V9a.5.5 0 0 1-.5.5H4.5a.5.5 0 0 1 0-1h3V5.6a.5.5 0 1 1 1 0"/>
                                    </svg>
                                    <div class="voiceip-button__desc voiceip-button__desc--viewbooking">Gần đây</div>
                                </div>
                                <div class="voiceip-button voiceip-mute">
                                    <svg width="24" height="24" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" fill="currentColor" stroke="#333" class="i-mute"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g><title>ionicons-v5-g</title><line x1="432" y1="400" x2="96" y2="64" style="fill:none;stroke:#333;stroke-linecap:square;stroke-miterlimit:10;stroke-width:32px"></line><path d="M368,192v48a111.74,111.74,0,0,1-2.93,25.45L390.65,291A143.07,143.07,0,0,0,400,240V192Z"></path><path d="M272,432V383.11a143.11,143.11,0,0,0,56.65-18.83L305,340.65A112.13,112.13,0,0,1,144,240V192H112v48c0,74,56.1,135.12,128,143.11V432H176v32H336V432Z"></path><path d="M336,236.37V128c0-44.86-35.14-80-80-80a79.68,79.68,0,0,0-69,39.34"></path><path d="M176,211.63V239a80.89,80.89,0,0,0,23.45,56.9,78.55,78.55,0,0,0,81,20.21Z"></path></g></svg>
                                    <svg width="24" height="24" viewBox="0 0 512.00 512.00" xmlns="http://www.w3.org/2000/svg" fill="currentColor" stroke="#333" class="i-unmute"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g><title>ionicons-v5-g</title><line x1="192" y1="448" x2="320" y2="448" style="fill:none;stroke:#333;stroke-linecap:square;stroke-miterlimit:10;stroke-width:32px"></line><path d="M384,208v32c0,70.4-57.6,128-128,128h0c-70.4,0-128-57.6-128-128V208" style="fill:none;stroke:#333;stroke-linecap:square;stroke-miterlimit:10;stroke-width:32px"></path><line x1="256" y1="368" x2="256" y2="448" style="fill:none;stroke:#333;stroke-linecap:square;stroke-miterlimit:10;stroke-width:32px"></line><path d="M256,320a78.83,78.83,0,0,1-56.55-24.1A80.89,80.89,0,0,1,176,239V128a79.69,79.69,0,0,1,80-80c44.86,0,80,35.14,80,80V239C336,283.66,300.11,320,256,320Z"></path></g></svg>
                                    <div class="voiceip-button__desc">Tắt âm</div>
                                </div>
                                <div class="voiceip-button voiceip-dtmf">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="6" r="2"></circle><circle cx="6" cy="6" r="2"></circle><circle cx="18" cy="6" r="2"></circle><circle cx="12" cy="12" r="2"></circle><circle cx="6" cy="12" r="2"></circle><circle cx="18" cy="12" r="2"></circle><circle cx="12" cy="18" r="2"></circle></svg>
                                    <div class="voiceip-button__desc">Bàn phím</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="popup-inforbooking"></div>
                </div>
            ';

            if($current_user->id == '1' || $current_user->id == '493ad5e5-ffea-a84f-96d7-6577fed623d6' || $current_user->id = '168889bb-54c2-59c7-8b3f-649102530d3c'){
                $css .= '<link rel="stylesheet" href="custom/jssip_webrtc/call2.css?ver=3.0">';
                $js .= '<audio id="audio_jssip"></audio>
                        <script src="custom/jssip_webrtc/jssip-3.9.4.min.js"></script>
                        <script src="custom/jssip_webrtc/call2.js?ver=3.0"></script>
                ';
            } else {
                $css .= '<link rel="stylesheet" href="custom/jssip_webrtc/call.css?ver=3.0">';
                $js .= '<audio id="audio_jssip"></audio>
                        <script src="custom/jssip_webrtc/jssip-3.9.4.min.js"></script>
                        <script src="custom/jssip_webrtc/call.js?ver=3.0"></script>
                ';
            }
        }
        else {
            $html .= '<input type="hidden" name="sip_user" id="sip_user" value="" disabled />';
            $html .= '<input type="hidden" name="sip_password" id="sip_password" value="" disabled />';
        }

        echo $css.$html.$js;
    }

    /**
     * Called from process(). This method will display subpanels.
     */
    protected function _displaySubPanels()
    {
        if (isset($this->bean) &&
            !empty($this->bean->id) &&
            (file_exists('modules/' . $this->module . '/metadata/subpaneldefs.php') ||
                file_exists('custom/modules/' . $this->module . '/metadata/subpaneldefs.php') ||
                file_exists('custom/modules/' . $this->module . '/Ext/Layoutdefs/layoutdefs.ext.php'))
        ) {
            $GLOBALS['focus'] = $this->bean;
            require_once('include/SubPanel/SubPanelTiles.php');
            $subpanel = new SubPanelTiles($this->bean, $this->module);
            echo $subpanel->display();
        }
    }

    protected function _buildModuleList()
    {
        if (!empty($GLOBALS['current_user']) && empty($GLOBALS['modListHeader'])) {
            $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);
        }
    }

    /**
     * private method used in process() to determine the value of a passed in option
     *
     * @param string option - the option that we want to know the valye of
     * @param bool default - what the default value should be if we do not find the option
     *
     * @return bool - the value of the option
     */
    protected function _getOption(
        $option,
        $default = false
    ) {
        if (!empty($this->options) && isset($this->options['show_all'])) {
            return $this->options['show_all'];
        } elseif (!empty($this->options) && isset($this->options[$option])) {
            return $this->options[$option];
        } else {
            return $default;
        }
    }

    /**
     * track
     * Private function to track information about the view request
     */
    private function _track()
    {
        if (empty($this->responseTime)) {
            $this->_calculateFooterMetrics();
        }
        if (empty($GLOBALS['current_user']->id)) {
            return;
        }

        $trackerManager = TrackerManager::getInstance();
        $trackerManager->save();
    }

    /**
     * Checks to see if the module name passed is valid; dies if it is not
     */
    protected function _checkModule()
    {
        if (!empty($this->module) && !file_exists('modules/' . $this->module)) {
            $error = str_replace("[module]", (string)$this->module, $GLOBALS['app_strings']['ERR_CANNOT_FIND_MODULE']);
            $GLOBALS['log']->fatal($error);
            echo $error;
            die();
        }
    }

    public function renderJavascript()
    {
        if ($this->action !== 'Login') {
            $this->_displayJavascript();
        } else {
            $this->_displayLoginJS();
        }
    }

    private function _calculateFooterMetrics()
    {
        $endTime = microtime(true);
        $deltaTime = $endTime - (isset($GLOBALS['startTime']) ? $GLOBALS['startTime'] : null);
        $this->responseTime = number_format(round($deltaTime, 2), 2);
        // Print out the resources used in constructing the page.
        $this->fileResources = count(get_included_files());
    }

    /**
     * @return string
     */
    private function _getStatistics()
    {
        $endTime = microtime(true);
        $deltaTime = $endTime - (isset($GLOBALS['startTime']) ? $GLOBALS['startTime'] : null);
        $response_time_string =
            $GLOBALS['app_strings']['LBL_SERVER_RESPONSE_TIME'] .
            ' ' .
            number_format(round($deltaTime, 2), 2) .
            ' ' .
            $GLOBALS['app_strings']['LBL_SERVER_RESPONSE_TIME_SECONDS'];
        $return = $response_time_string. '<br />';

        if (!empty($GLOBALS['sugar_config']['show_page_resources'])) {
            // Print out the resources used in constructing the page.
            $included_files = get_included_files();

            // take all of the included files and make a list that does not allow for duplicates based on case
            // I believe the full get_include_files result set appears to have one entry for each file in real
            // case, and one entry in all lower case.
            $list_of_files_case_insensitive = array();
            foreach ($included_files as $key => $name) {
                // preserve the first capitalization encountered.
                $list_of_files_case_insensitive[mb_strtolower($name)] = $name;
            }
            $return .= $GLOBALS['app_strings']['LBL_SERVER_RESPONSE_RESOURCES'] .
                '(' .
                DBManager::getQueryCount() .
                ',' .
                count($list_of_files_case_insensitive) .
                ')<br>';
            // Display performance of the internal and external caches....
            $cacheStats = SugarCache::instance()->getCacheStats();
            $return .= "External cache (hits/total=ratio) local ({$cacheStats['localHits']}/{$cacheStats['requests']}=" .
                round($cacheStats['localHits'] * 100 / $cacheStats['requests'], 0) .
                "%)";
            $return .= " external ({$cacheStats['externalHits']}/{$cacheStats['requests']}=" .
                round($cacheStats['externalHits'] * 100 / $cacheStats['requests'], 0) .
                "%)<br />";
            $return .= " misses ({$cacheStats['misses']}/{$cacheStats['requests']}=" .
                round($cacheStats['misses'] * 100 / $cacheStats['requests'], 0) .
                "%)<br />";
        }

        $return .= $this->logMemoryStatistics();

        return $return;
    }

    /**
     * logMemoryStatistics
     *
     * This function returns a string message containing the memory statistics as well as writes to the memory_usage.log
     * file the memory statistics for the SugarView invocation.
     *
     * @param $newline String of newline character to use (defaults to </ br>)
     *
     * @return string formatted message about memory statistics
     */
    protected function logMemoryStatistics($newline = '<br>')
    {
        $log_message = '';

        if (!empty($GLOBALS['sugar_config']['log_memory_usage'])) {
            if (function_exists('memory_get_usage')) {
                $memory_usage = memory_get_usage();
                $bytes = $GLOBALS['app_strings']['LBL_SERVER_MEMORY_BYTES'];
                $data = array($memory_usage, $bytes);
                $log_message = string_format($GLOBALS['app_strings']['LBL_SERVER_MEMORY_USAGE'], $data) . $newline;
            }

            if (function_exists('memory_get_peak_usage')) {
                $memory_peak_usage = memory_get_peak_usage();
                $bytes = $GLOBALS['app_strings']['LBL_SERVER_MEMORY_BYTES'];
                $data = array($memory_peak_usage, $bytes);
                $log_message .= string_format($GLOBALS['app_strings']['LBL_SERVER_PEAK_MEMORY_USAGE'], $data) .
                    $newline;
            }

            if (!empty($log_message)) {
                $data = array(
                    !empty($this->module) ? $this->module : $GLOBALS['app_strings']['LBL_LINK_NONE'],
                    !empty($this->action) ? $this->action : $GLOBALS['app_strings']['LBL_LINK_NONE'],
                );

                $output = string_format($GLOBALS['app_strings']['LBL_SERVER_MEMORY_LOG_MESSAGE'], $data) . $newline;
                $output .= $log_message;
                $fp = fopen("memory_usage.log", "ab");
                fwrite($fp, $output);
                fclose($fp);
            }
        }

        return $log_message;
    }

    /**
     * Loads the module shortcuts menu
     *
     * @param  $module string optional, can specify module to retrieve menu for if not the current one
     *
     * @return array module menu
     */
    public function getMenu(
        $module = null
    ) {
        global $current_language, $mod_strings, $app_strings, $module_menu;

        if (empty($module)) {
            $module = $this->module;
        }

        //Need to make sure the mod_strings match the requested module or Menus may fail
        $curr_mod_strings = $mod_strings;
        $mod_strings = return_module_language($current_language, $module);

        $module_menu = array();

        if (file_exists(get_custom_file_if_exists('modules/' . $module . '/Menu.php'))) {
            require(get_custom_file_if_exists('modules/' . $module . '/Menu.php'));
        }
        if (file_exists('custom/modules/' . $module . '/Ext/Menus/menu.ext.php')) {
            require('custom/modules/' . $module . '/Ext/Menus/menu.ext.php');
        }
        if (!file_exists(get_custom_file_if_exists('modules/' . $module . '/Menu.php')) &&
            !file_exists('custom/modules/' . $module . '/Ext/Menus/menu.ext.php') &&
            !empty($GLOBALS['mod_strings']['LNK_NEW_RECORD'])
        ) {
            $module_menu[] = array(
                "index.php?module=$module&action=EditView&return_module=$module&return_action=DetailView",
                $GLOBALS['mod_strings']['LNK_NEW_RECORD'],
                'Create',
                $module
            );
            $module_menu[] = array(
                "index.php?module=$module&action=index",
                $GLOBALS['mod_strings']['LNK_LIST'],
                'List',
                $module
            );
            if (($this->bean instanceof SugarBean) && !empty($this->bean->importable)) {
                if (!empty($mod_strings['LNK_IMPORT_' . strtoupper($module)])) {
                    $module_menu[] = array(
                        "index.php?module=Import&action=Step1&import_module=$module&return_module=$module&return_action=index",
                        $mod_strings['LNK_IMPORT_' . strtoupper($module)],
                        "Import",
                        $module
                    );
                } else {
                    $module_menu[] = array(
                        "index.php?module=Import&action=Step1&import_module=$module&return_module=$module&return_action=index",
                        $app_strings['LBL_IMPORT'],
                        "Import",
                        $module
                    );
                }
            }
        }
        if (file_exists('custom/application/Ext/Menus/menu.ext.php')) {
            require('custom/application/Ext/Menus/menu.ext.php');
        }

        $mod_strings = $curr_mod_strings;
        $builtModuleMenu = $module_menu;
        unset($module_menu);

        return $builtModuleMenu;
    }

    /**
     * Returns the module name which should be highlighted in the module menu
     */
    protected function _getModuleTab()
    {
        global $app_list_strings, $moduleTabMap, $current_user;

        $userTabs = query_module_access_list($current_user);
        $defaultTab = (in_array("Home", $userTabs)) ? "Home" : key($userTabs);

        if (!empty($_REQUEST['module_tab'])) {
            return $_REQUEST['module_tab'];
        } elseif (isset($moduleTabMap[$this->module])) {
            return $moduleTabMap[$this->module];
        } // Special cases
        elseif ($this->module == 'MergeRecords') {
            return !empty($_REQUEST['merge_module']) ? $_REQUEST['merge_module'] : $_REQUEST['return_module'];
        } elseif ($this->module == 'Users' && $this->action == 'SetTimezone') {
            return $defaultTab;
        } // Default anonymous pages to be under Home
        elseif (!isset($app_list_strings['moduleList'][$this->module])) {
            return $defaultTab;
        } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "ajaxui") {
            return $defaultTab;
        } else {
            return $this->module;
        }
    }

    /**
     * Return the "breadcrumbs" to display at the top of the page
     *
     * @param  bool $show_help optional, true if we show the help links
     *
     * @return HTML string containing breadcrumb title
     */
    public function getModuleTitle(
        $show_help = true
    ) {
        global $sugar_version, $sugar_flavor, $server_unique_key, $current_language, $action;

        $theTitle = "<div class='moduleTitle moduleTitle-sugarview__in-includes'>\n";

        $module = preg_replace("/ /", "", $this->module);

        $params = $this->_getModuleTitleParams();
        $index = 0;

        if (SugarThemeRegistry::current()->directionality == "rtl") {
            $params = array_reverse($params);
        }
        if (count($params) > 1) {
            array_shift($params);
        }
        $count = count($params);
        $paramString = '';
        foreach ($params as $parm) {
            $index++;
            $paramString .= $parm;
            if ($index < $count) {
                $paramString .= $this->getBreadCrumbSymbol();
            }
        }

        // if (!empty($paramString)) {
        //     $theTitle .= "<h2 class='module-title-text'> $paramString </h2>";

        //     if ($this->type == "detail") {
        //         $theTitle .= "<div class='favorite' record_id='" .
        //             $this->bean->id .
        //             "' module='" .
        //             $this->bean->module_dir .
        //             "'><div class='favorite_icon_outline'>" .
        //             "<span class='suitepicon suitepicon-favorite-star-outline'></span></div>
        //                                             <div class='favorite_icon_fill' 'title=\"' . translate('LBL_DASHLET_EDIT', 'Home') . '\" border=\"0\"  align=\"absmiddle\"'>" .

        //             "<span class='suitepicon suitepicon-favorite-star'></span></div></div>";
        //     }
        // }
        if (!empty($paramString)) {
            $theTitle .= "<h2 class='module-title-text'> $paramString </h2>";
        }

        // bug 56131 - restore conditional so that link doesn't appear where it shouldn't
        if ($show_help || $this->type == 'list') {
            $theTitle .= "<span class='utils'>";
            // $createImageURL = SugarThemeRegistry::current()->getImageURL('create-record.gif');
            $createImageURL = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                            </svg>';
            if ($this->type == 'list') {
                $theTitle .= '<a href="#" class="btn btn-success showsearch"><span class=" glyphicon glyphicon-search" aria-hidden="true"></span></a>';
            }
            $url = ajaxLink("index.php?module=$module&action=EditView&return_module=$module&return_action=DetailView");
            if ($show_help) {
                $theTitle .= <<<EOHTML
&nbsp;
<a id="create_image" href="{$url}" class="utilsLink">
$createImageURL
<a id="create_link" href="{$url}" class="utilsLink">
{$GLOBALS['app_strings']['LNK_CREATE']}
</a>
EOHTML;
            }
            $theTitle .= "</span>";
        }

        // Custom icon filter - listview
        if($this->action == "ListView"){
            $theTitle .= '<svg id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16"><path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"></path></svg>';
        }

        $theTitle .= "</div>\n";

        return $theTitle;
    }

    /**
     * Return the metadata file that will be used by this view.
     *
     * @return string File location of the metadata file.
     */
    public function getMetaDataFile()
    {
        $metadataFile = null;
        $foundViewDefs = false;
        $viewDef = strtolower($this->type) . 'viewdefs';
        $coreMetaPath = 'modules/' . $this->module . '/metadata/' . $viewDef . '.php';
        if (file_exists('custom/' . $coreMetaPath)) {
            $metadataFile = 'custom/' . $coreMetaPath;
            $foundViewDefs = true;
        } else {
            if (file_exists('custom/modules/' . $this->module . '/metadata/metafiles.php')) {
                require_once('custom/modules/' . $this->module . '/metadata/metafiles.php');
                if (!empty($metafiles[$this->module][$viewDef])) {
                    $metadataFile = $metafiles[$this->module][$viewDef];
                    $foundViewDefs = true;
                }
            } elseif (file_exists('modules/' . $this->module . '/metadata/metafiles.php')) {
                require_once('modules/' . $this->module . '/metadata/metafiles.php');
                if (!empty($metafiles[$this->module][$viewDef])) {
                    $metadataFile = $metafiles[$this->module][$viewDef];
                    $foundViewDefs = true;
                }
            }
        }

        if (!$foundViewDefs && file_exists($coreMetaPath)) {
            $metadataFile = $coreMetaPath;
        }
        $GLOBALS['log']->debug("metadatafile=" . $metadataFile);

        return $metadataFile;
    }

    /**
     * Returns an array composing of the breadcrumbs to use for the module title
     *
     * @param bool $browserTitle true if the returned string is being used for the browser title, meaning
     *                           there should be no HTML in the string
     *
     * @return array
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        $params = array($this->_getModuleTitleListParam($browserTitle));
        if (isset($this->action)) {
            switch ($this->action) {
                case 'EditView':
                    if (!empty($this->bean->id) &&
                        (empty($_REQUEST['isDuplicate']) || $_REQUEST['isDuplicate'] === 'false')
                    ) {
                        $params[] =
                            "<a href='index.php?module={$this->module}&action=DetailView&record={$this->bean->id}'>" .
                            $this->bean->get_summary_text() .
                            "</a>";
                        $params[] = $GLOBALS['app_strings']['LBL_EDIT_BUTTON_LABEL'];
                    } else {
                        $params[] = $GLOBALS['app_strings']['LBL_CREATE_BUTTON_LABEL'];
                    }
                    break;
                case 'DetailView':
                    $beanName = $this->bean->get_summary_text();
                    $params[] = $beanName;
                    break;
            }
        }


        return $params;
    }

    /**
     * Returns the portion of the array that will represent the listview in the breadcrumb
     *
     * @param bool $browserTitle true if the returned string is being used for the browser title, meaning
     *                           there should be no HTML in the string
     *
     * @return string
     */
    protected function _getModuleTitleListParam($browserTitle = false)
    {
        global $app_strings;

        if (!empty($GLOBALS['app_list_strings']['moduleList'][$this->module])) {
            $firstParam = $GLOBALS['app_list_strings']['moduleList'][$this->module];
        } else {
            $firstParam = $this->module;
        }

        $iconPath = $this->getModuleTitleIconPath($this->module);
        if ($this->action == "ListView" || $this->action == "index") {
            if (!empty($iconPath) && !$browserTitle) {
                if (SugarThemeRegistry::current()->directionality == "ltr") {
                    return $app_strings['LBL_SEARCH_ALT'] . "&nbsp;"
                        . (string)$firstParam;
                }
                return (string)$firstParam . "&nbsp;" . $app_strings['LBL_SEARCH'];
            }
            return $firstParam;
        }
        if (!empty($iconPath) && !$browserTitle) {
            //return "<a href='index.php?module={$this->module}&action=index'>$this->module</a>";
        } else {
            return $firstParam;
        }
    }

    /**
     * @param $module
     *
     * @return string
     */
    protected function getModuleTitleIconPath($module)
    {
        $iconPath = '';
        if (is_file(SugarThemeRegistry::current()->getImageURL('icon_' . $module . '_32.png', false))) {
            $iconPath = SugarThemeRegistry::current()->getImageURL('icon_' . $module . '_32.png');
        } elseif (is_file(SugarThemeRegistry::current()->getImageURL('icon_' . ucfirst($module) . '_32.png', false))) {
            $iconPath = SugarThemeRegistry::current()->getImageURL('icon_' . ucfirst($module) . '_32.png');
        } elseif (is_file(SugarThemeRegistry::current()->getImageURL('icon_' . $module . '_32.svg', false))) {
            $iconPath = SugarThemeRegistry::current()->getImageURL('icon_' . $module . '_32.svg');
        } elseif (is_file(SugarThemeRegistry::current()->getImageURL('icon_' . ucfirst($module) . '_32.svg', false))) {
            $iconPath = SugarThemeRegistry::current()->getImageURL('icon_' . ucfirst($module) . '_32.svg');
        }

        return $iconPath;
    }

    /**
     * Returns the string which will be shown in the browser's title; defaults to using the same breadcrumb
     * as in the module title
     *
     * @return string
     */
    public function getBrowserTitle()
    {
        global $app_strings;

        $browserTitle = $app_strings['LBL_BROWSER_TITLE'];
        if ($this->module == 'Users' && ($this->action == 'SetTimezone' || $this->action == 'Login')) {
            return $browserTitle;
        }
        $params = $this->_getModuleTitleParams(true);
        foreach ($params as $value) {
            $browserTitle = strip_tags($value) . ' &raquo; ' . $browserTitle;
        }

        return $browserTitle;
    }

    /**
     * Returns the correct breadcrumb symbol according to theme's directionality setting
     *
     * @return string
     */
    public function getBreadCrumbSymbol()
    {
        if (SugarThemeRegistry::current()->directionality == "ltr") {
            return "<span class='pointer'>&raquo;</span>";
        } else {
            return "<span class='pointer'>&laquo;</span>";
        }
    }

    /**
     * Fetch config values to be put into an array for JavaScript
     *
     * @return array
     */
    protected function getSugarConfigJS()
    {
        global $sugar_config;

        // Set all the config parameters in the JS config as necessary
        $config_js = array();
        // AjaxUI stock banned modules
        $config_js[] = "SUGAR.config.stockAjaxBannedModules = " . json_encode(ajaxBannedModules()) . ";";
        if (isset($sugar_config['quicksearch_querydelay'])) {
            $config_js[] =
                $this->prepareConfigVarForJs('quicksearch_querydelay', $sugar_config['quicksearch_querydelay']);
        }
        if (empty($sugar_config['disableAjaxUI'])) {
            $config_js[] = "SUGAR.config.disableAjaxUI = false;";
        } else {
            $config_js[] = "SUGAR.config.disableAjaxUI = true;";
        }
        if (!empty($sugar_config['addAjaxBannedModules'])) {
            $config_js[] = $this->prepareConfigVarForJs('addAjaxBannedModules', $sugar_config['addAjaxBannedModules']);
        }
        if (!empty($sugar_config['overrideAjaxBannedModules'])) {
            $config_js[] =
                $this->prepareConfigVarForJs('overrideAjaxBannedModules', $sugar_config['overrideAjaxBannedModules']);
        }
        if (!empty($sugar_config['js_available']) && is_array($sugar_config['js_available'])) {
            foreach ($sugar_config['js_available'] as $configKey) {
                if (isset($sugar_config[$configKey])) {
                    $jsVariableStatement = $this->prepareConfigVarForJs($configKey, $sugar_config[$configKey]);
                    if (!array_search($jsVariableStatement, $config_js)) {
                        $config_js[] = $jsVariableStatement;
                    }
                }
            }
        }

        return $config_js;
    }

    /**
     * Utility method to convert sugar_config values into a JS acceptable format.
     *
     * @param string $key Config Variable Name
     * @param string $value Config Variable Value
     *
     * @return string
     */
    protected function prepareConfigVarForJs($key, $value)
    {
        $value = json_encode($value);

        return "SUGAR.config.{$key} = {$value};";
    }

    /**
     * getHelpText
     *
     * This is a protected function that returns the help text portion.  It is called from getModuleTitle.
     *
     * @param $module String the formatted module name
     *
     * @return string the HTML for the help text
     */
    protected function getHelpText($module)
    {
        // $createImageURL = SugarThemeRegistry::current()->getImageURL('create-record.gif');
        $createImageURL = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                            </svg>';
        $url = ajaxLink("index.php?module=$module&action=EditView&return_module=$module&return_action=DetailView");
        $theTitle = <<<EOHTML
        $createImageURL
        <a href="{$url}" class="utilsLink">
            {$GLOBALS['app_strings']['LNK_CREATE']}
        </a>
EOHTML;

        return $theTitle;
    }

    /**
     * Retrieves favicon corresponding to currently requested module
     *
     * @return array
     */
    protected function getFavicon()
    {
        // get favicon
        if (isset($GLOBALS['sugar_config']['default_module_favicon'])) {
            $module_favicon = $GLOBALS['sugar_config']['default_module_favicon'];
        } else {
            $module_favicon = false;
        }

        $themeObject = SugarThemeRegistry::current();

        $favicon = '';
        if ($module_favicon) {
            $favicon = $themeObject->getImageURL($this->module . '.gif', false);
        }
        if (!is_file($favicon) || !$module_favicon) {
            // $favicon = $themeObject->getImageURL('sugar_icon.ico', false);
            // $favicon = $themeObject->getImageURL('favicon-96.png', false);
            $favicon = $themeObject->getImageURL('suitenp_fav.png', false);

        }

        $extension = pathinfo($favicon, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'png':
                $type = 'image/png';
                break;
            case 'ico':
                // fall through
            default:
                $type = 'image/x-icon';
                break;
        }

        return array(
            'url' => getJSPath($favicon),
            'type' => $type,
        );
    }

    /**
     * getCustomFilePathIfExists
     *
     * This function wraps a call to get_custom_file_if_exists from include/utils.php
     *
     * @param $file String of filename to check
     *
     * @return string filename including custom directory if found
     */
    protected function getCustomFilePathIfExists($file)
    {
        return get_custom_file_if_exists($file);
    }

    /**
     * fetchTemplate
     *
     * This function wraps the call to the fetch function of the Smarty variable for the view
     *
     * @param $file String path of the file to fetch
     *
     * @return string content from resulting Smarty fetch operation on template
     */
    protected function fetchTemplate($file)
    {
        return $this->ss->fetch($file);
    }

    /**
     * handles the tracker output, and adds a link and a shortened name.
     * given html safe input, it will preserve html safety
     *
     * @param array $history - returned from the tracker
     *
     * @return array augmented history with image link and shortened name
     */
    protected function processRecentRecords($history)
    {
        foreach ($history as $key => $row) {
            $history[$key]['item_summary_short'] =
                to_html(getTrackerSubstring($row['item_summary'])); //bug 56373 - need to re-HTML-encode
            $history[$key]['image'] =
                SugarThemeRegistry::current()->getImage(
                    $row['module_name'],
                    'border="0" align="absmiddle"',
                    null,
                    null,
                    '.gif',
                    $row['item_summary']
                );
        }

        return $history;
    }

    /**
     * Determines whether the state of the post global array indicates there was an error uploading a
     * file that exceeds the post_max_size setting.  Such an error can be detected if:
     *  1. The Server['REQUEST_METHOD'] will still point to POST
     *  2. POST and FILES global arrays will be returned empty despite the request method
     * This also results in a redirect to the home page (due to lack of module and action in POST)
     *
     * @return boolean indicating true or false
     */
    public function checkPostMaxSizeError()
    {
        //if the referrer is post, and the post array is empty, then an error has occurred, most likely
        //while uploading a file that exceeds the post_max_size.
        if (empty($_FILES) &&
            empty($_POST) &&
            isset($_SERVER['REQUEST_METHOD']) &&
            strtolower($_SERVER['REQUEST_METHOD']) == 'post'
        ) {
            $GLOBALS['log']->fatal($GLOBALS['app_strings']['UPLOAD_ERROR_HOME_TEXT']);

            return true;
        }

        return false;
    }

    /**
     *
     * @param array $data
     * @param string $scope
     * @return bool
     */
    public function addDomJS($data, $scope)
    {
        $ret = self::NO_ERROR;
        if (!$scope) {
            throw new InvalidArgumentException('Scope can not be empty', self::ERR_EMPTY_SCOPE);
        }
        if (isset($this->settings[$scope])) {
            LoggerManager::getLogger()->warn('Scope "' . $scope . '" already exists but it will be overwriten.');
            $ret = self::WARN_SCOPE_EXISTS;
        }
        $this->settings[$scope] = $this->mergeDeepArray($data);
        return $ret;
    }

    /**
     *
     * @return string
     */
    public function getDomJS()
    {
        $ret = json_encode($this->settings);
        if ($ret === false) {
            $err = json_last_error();
            if ($err) {
                throw new Exception('JSON Error occured: #' . $err  . ' - ' . json_last_error_msg());
            }
        }
        return $ret;
    }

    /**
     *
     * @return bool
     */
    public function hasDomJS()
    {
        return !empty($this->settings);
    }

    /**
     * Merges multiple arrays, recursively, and returns the merged array.
     * https://api.drupal.org/api/drupal/includes!bootstrap.inc/function/drupal_array_merge_deep_array/7
     *
     * @param array $arrays
     * @return array
     */
    public function mergeDeepArray($arrays)
    {
        $result = array();

        if (!is_array($arrays)) {
            throw new InvalidArgumentException('Parameter should be an array to merging. ' . gettype($arrays) . ' given.', self::ERR_NOT_ARRAY);
        }

        foreach ($arrays as $array) {
            $result = $this->getNextResultsForDeepMerge($array, $result);
        }

        return $result;
    }

    /**
     *
     * @param array $array
     * @param array $result
     * @return array
     */
    protected function getNextResultsForDeepMerge($array, $result)
    {
        if (!is_array($array)) {
            throw new InvalidArgumentException('Sub-parameter should be an array to merging. ' . gettype($array) . ' given.', self::ERR_NOT_SUB_ARRAY);
        }
        foreach ($array as $key => $value) {
            // Renumber integer keys as array_merge_recursive() does. Note that PHP
            // automatically converts array keys that are integer strings (e.g., '1')
            // to integers.
            if (is_integer($key)) {
                $result[] = $value;
            } elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                // Recurse when both values are arrays.
                $result[$key] = $this->mergeDeepArray([$result[$key], $value]);
            } else {
                // Otherwise, use the latter value, overriding any previous value.
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     *
     * @param string $module_dir
     * @return array
     */
    public function getVardefsData($module_dir)
    {
        if (!$module_dir) {
            throw new InvalidArgumentException('Module DIR can not be empty', self::ERR_EMPTY_MODULE_DIR);
        }
        $data = array();
        $bean = SugarModule::get($module_dir)->loadBean();

        if ($bean) {
            foreach ($bean->field_defs as $field_name => $def) {
                $data[$module_dir][$field_name] = $def;
                if (isset($def['required'])) {
                    $data[$module_dir][$field_name]['required'] = $def['required'];
                } else {
                    $data[$module_dir][$field_name]['required'] = false;
                }
            }
        } else {
            LoggerManager::getLogger()->warn('Could not retrive a bean from DIR: ' . $module_dir);
        }
        unset($bean);
        return array($data);
    }
}
