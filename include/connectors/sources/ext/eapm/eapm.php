<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/connectors/sources/default/source.php');

/**
 * Generic source connected using EAPM access details
 * @api
 */
abstract class ext_eapm extends source
{

    /**
     * The ExternalAPI Base that instantiated this connector.
     * @var _eapm
     */
    protected $_eapm;

    public function setEAPM(ExternalAPIBase $eapm)
    {
        $GLOBALS['log']->debug("Connector is setting eapm");
        $this->_eapm = $eapm;
    }

    public function getEAPM()
    {
        $GLOBALS['log']->debug("Connector is getting eapm");
        return $this->_eapm;
    }
}
