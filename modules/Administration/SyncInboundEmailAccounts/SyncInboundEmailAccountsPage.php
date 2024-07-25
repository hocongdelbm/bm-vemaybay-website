<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SyncInboundEmailAccountsPage
 *
 * Handle the page of 'Sync Inbound Email Accounts' menu item in Admin page / Repair section
 * - handle the current sub/ajax actions
 * - sync email-UID and orphaned field in email module
 *
 */
class SyncInboundEmailAccountsPage
{

    /**
     * @var array
     */
    protected $includeData;

    /**
     * @var Sugar_Smarty
     */
    protected $tpl;

    /**
     * SyncInboundEmailAccountsPage constructor.
     *
     * The $includeData parameter should contains all variable
     * in php included file by action, use get_defined_vars()
     * The class handle a sub-action called method, use $_REQUEST['method']
     *
     * @param array $includeData
     */
    public function __construct($includeData)
    {

        // create object state

        $this->includeData = $includeData;
        $this->tpl = new Sugar_Smarty();
        $this->tpl->assign('app_strings', $this->includeData['app_strings']);

        // handle the sub-action

        new SyncInboundEmailAccountsSubActionHandler($this);
    }

    /**
     * Show basic UI for Sync Inbound Email Accounts
     *
     * @param $ieList
     */
    public function showForm($ieList)
    {
        $this->tpl->assign('ieList', $ieList);
        $this->tpl->display('modules/Administration/templates/SyncInboundEmailAccounts.tpl');
    }

    /**
     * @param string $output
     */
    public function showOutput($output)
    {
        echo $output;
    }
}
