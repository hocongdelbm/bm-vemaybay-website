<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Sugarpdf/Sugarpdf.php');

/**
 * This is an helper class to generate PDF using smarty template.
 * You have to extend this class, set the templateLocation to your smarty template
 * location and assign the Smarty variables ($this->ss->assign()) in the overriden
 * preDisplay method (don't forget to call the parent).
 *
 * @author bsoufflet
 *
 */
class SugarpdfSmarty extends Sugarpdf
{

    /**
     *
     * @var String
     */
    protected $templateLocation = "";
    /**
     * The Sugar_Smarty object
     * @var Sugar_Smarty
     */
    protected $ss;
    /**
     * These 5 variables are use for the writeHTML method.
     * @see include/tcpdf/tcpdf.php writeHTML()
     */
    protected $smartyLn = true;
    protected $smartyFill = false;
    protected $smartyReseth = false;
    protected $smartyCell = false;
    protected $smartyAlign = "";

    public function preDisplay()
    {
        parent::preDisplay();
        $this->print_header = false;
        $this->print_footer = false;
        $this->_initSmartyInstance();
    }

    public function display()
    {
        //turn off all error reporting so that PHP warnings don't munge the PDF code
        $maxExecutionTime = ini_get('max_execution_time');
        $errorReporting = error_reporting();

        error_reporting(0);
        set_time_limit(1800);

        //Create new page
        $this->AddPage();
        $this->SetFont(PDF_FONT_NAME_MAIN, '', 8);

        if (!empty($this->templateLocation)) {
            $str = $this->ss->fetch($this->templateLocation);
            $this->writeHTML($str, $this->smartyLn, $this->smartyFill, $this->smartyReseth, $this->smartyCell, $this->smartyAlign);
        } else {
            $this->Error('The class SugarpdfSmarty has to be extended and you have to set a location for the Smarty template.');
        }

        ini_set('max_execution_time', $maxExecutionTime);
        error_reporting($errorReporting);
    }

    /**
     * Init the Sugar_Smarty object.
     */
    private function _initSmartyInstance()
    {
        if (!($this->ss instanceof Sugar_Smarty)) {
            require_once('include/Sugar_Smarty.php');
            $this->ss = new Sugar_Smarty();
            $this->ss->assign('MOD', $GLOBALS['mod_strings']);
            $this->ss->assign('APP', $GLOBALS['app_strings']);
        }
    }
}
