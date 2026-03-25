<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class BaseHandler
{
    /**
     *
     * @var string
     */
    protected $tplPath = '';

    /**
     *
     * @var Sugar_Smarty
     */
    protected $ss = null;

    /**
     *
     * @var array
     */
    protected $request = null;

    /**
     *
     * @var array
     */
    protected $modStrings = null;

    /**
     * Set up the object
     *
     * @param Sugar_Smarty $sugar_smarty
     * @param array        $request
     * @param array        $mod_strings
     */
    public function __construct(Sugar_Smarty $sugar_smarty, $request, $mod_strings)
    {
        $this->ss          = $sugar_smarty;
        $this->request     = $request;
        $this->modStrings  = $mod_strings;

        $this->getLanguage();
        $this->getJavascipt();
    }

    /**
     * Get Languages
     *
     * @return void
     */
    protected function getLanguage()
    {
        $this->ss->assign('LANGUAGES', $this->protectedLanguage());
    }

    /**
     * Get Javascript
     *
     * @return void
     */
    protected function getJavascipt()
    {
        $this->ss->assign("JAVASCRIPT", $this->protectedJavascript());
    }

    /**
     * protected function the languages
     *
     * @return array
     */
    protected function protectedLanguage()
    {
        return get_languages();
    }

    /**
     * protected function for javascript
     *
     * @return array
     */
    protected function protectedJavascript()
    {
        return get_set_focus_js();
    }

    /**
     * protected function for SugarApplication::redirect() so test mock can override it
     *
     * @param string $url
     */
    protected function redirect($url)
    {
        SugarApplication::redirect($url);
    }

    /**
     * protected function for exit so test mock can override it
     *
     * @return void
     */
    protected function protectedExit()
    {
        exit;
    }

    /**
     * protected function for die() so test mock can override it
     *
     * @param string $exitstring
     */
    protected function protectedDie($exitstring)
    {
        die($exitstring);
    }
}
