<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class GoogleCalendarSettingsHandler extends BaseHandler
{
    /**
     *
     * @var Configurator
     */
    protected $configurator = null;

    /**
     *
     * @var javascript
     */
    protected $js = null;

    /**
     *
     * @var User
     */
    protected $currentUser = null;

    /**
     * Setup Object
     *
     * @param string       $tpl_path
     * @param User         $current_user
     * @param array        $request
     * @param array        $mod_strings
     * @param Configurator $config
     * @param Sugar_Smarty $sugar_smarty
     * @param javascript   $js
     */
    public function __construct($tpl_path, User $current_user, $request, $mod_strings, Configurator $config, Sugar_Smarty $sugar_smarty, javascript $js)
    {
        // Get parent
        parent::__construct($sugar_smarty, $request, $mod_strings);

        $this->currentUser  = $current_user;
        $this->tplPath      = $tpl_path;
        $this->js           = $js;
        $this->configurator = $config;

        $this->checkUserIsAdmin();

        $this->doActions();
        $this->handleDisplay();
    }

    /**
     * Check the current user is admin
     *
     * @return void
     */
    protected function checkUserIsAdmin()
    {
        // Check current user is admin
        if (!is_admin($this->currentUser)) {
            $this->protectedDie("Unauthorized access to administration.");
        }
    }

    /**
     * Deal with do actions
     *
     * @return void
     */
    protected function doActions()
    {
        if (isset($this->request['do']) && $this->request['do'] == 'save') {
            $this->configurator->config['google_auth_json'] = !empty($this->request['google_auth_json']);
            $this->configurator->saveConfig();
            $this->redirect('index.php?module=Administration&action=index');
            $this->protectedExit();
        }
    }

    /**
     * This function handles displaying the template
     *
     * @return void
     */
    public function handleDisplay()
    {
        $this->ss->assign('PAGE_TITLE', $this->getPageTitle());

        $this->getJavascript();
        $this->getGoogleCalendarAuthState();

        $this->ss->display($this->tplPath);
    }

    /**
     * Get the page title
     *
     * @return string
     */
    protected function getPageTitle()
    {
        return getClassicModuleTitle(
            "Administration",
            array(
                "<a href='index.php?module=Administration&action=index'>" . translate('LBL_MODULE_NAME', 'Administration') . "</a>",
                $this->modStrings['LBL_GOOGLE_AUTH_TITLE'],
            ),
            false
        );
    }

    /**
     * Get the google calendar authentication state
     *
     * @return void
     */
    protected function getGoogleCalendarAuthState()
    {
        // Get the config
        $this->getConfig();

        // Check for Google Sync JSON
        $json = base64_decode($this->configurator->config['google_auth_json']);
        $gcConfig = json_decode($json, true);

        $googleJsonConfState = array(
            'status' => 'UNCONFIGURED',
            'color'  => 'black'
        );

        if ($gcConfig) {
            $googleJsonConfState = array(
                'status' => 'CONFIGURED',
                'color'  => 'green'
            );
        }

        $this->ss->assign('GOOGLE_JSON_CONF', $googleJsonConfState);
    }

    /**
     * Get the config
     *
     * @return void
     */
    protected function getConfig()
    {
        if (!array_key_exists('google_auth_json', $this->configurator->config)) {
            $this->configurator->config['google_auth_json'] = false;
        }

        $this->ss->assign('config', $this->configurator->config['google_auth_json']);
    }

    /**
     * Get the javascript
     *
     * @return void
     */
    protected function getJavascript()
    {
        $this->js->setFormName('ConfigureSettings');
        $js = $this->js->getScript();
        $this->ss->assign('JAVASCRIPT', $js);
    }
}
