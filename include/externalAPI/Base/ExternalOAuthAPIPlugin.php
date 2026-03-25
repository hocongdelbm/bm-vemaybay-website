<?php



/**
 * External API based on Oauth
 * @api
 */
interface ExternalOAuthAPIPlugin
{
    /**
     * Get OAuth parameters, to create OAuth client
     * @return array
     */
    public function getOauthParams();
    /**
     * Get OAuth request URL
     * @return string
     */
    public function getOauthRequestURL();
    /**
     * Get OAuth authorization URL
     * @return string
     */
    public function getOauthAuthURL();
    /**
     * Get OAuth access URL
     * @return string
     */
    public function getOauthAccessURL();
}
