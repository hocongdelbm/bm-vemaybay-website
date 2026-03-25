<?php



/**
 * External API interface
 * @api
 */
interface ExternalAPIPlugin
{
    /**
     * Check if this API supports certain authentication method
     * If $method is empty, return the list of supported methods
     * @param string $method
     * @return array|bool
     */
    public function supports($method = '');
    /**
     * Load data from EAPM bean
     * @param EAPM $eapmBean
     */
    public function loadEAPM($eapmBean);
    /**
     * Check if the data from the bean are good for login
     * @param EAPM $eapmBean
     * @return bool
     */
    public function checkLogin($eapmBean = null);
    /**
     * Log out from the service
     */
    public function logOff();
}
