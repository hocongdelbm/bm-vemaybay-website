<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Connector's HTML helper factory
 * @api
 */
class ConnectorHtmlHelperFactory
{
    const CONNECTOR_HTML_HELPER_MAIN = 'include/connectors/utils/ConnectorHtmlHelper.php';
    const CONNECTOR_HTML_HELPER_CUSTOM = 'custom/include/connectors/utils/ConnectorHtmlHelper.php';

    /**
     * Return instance of HTML helper class
     *
     * @return ConnectorHtmlHelper
     */
    public static function build()
    {
        if (file_exists(self::CONNECTOR_HTML_HELPER_CUSTOM)) {
            require_once(self::CONNECTOR_HTML_HELPER_CUSTOM);
        } else {
            require_once(self::CONNECTOR_HTML_HELPER_MAIN);
        }
        return new ConnectorHtmlHelper();
    }
}
