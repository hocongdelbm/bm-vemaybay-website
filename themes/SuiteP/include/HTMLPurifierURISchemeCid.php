<?php

namespace SuiteCRM;

/**
 * Class HTMLPurifierURISchemeCid
 * @package SuiteCRM
 * content-id: scheme implementation
 */
class HTMLPurifierURISchemeCid extends \HTMLPurifier_URIScheme
{
    /** @var bool $browsable */
    public $browsable = true;
    /** @var bool $may_omit_host */
    public $may_omit_host = true;

    /**
     * @param \HTMLPurifier_URI $uri
     * @param \HTMLPurifier_Config $config
     * @param \HTMLPurifier_Context $context
     * @return bool
     */
    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        $uri->port = null;
        $uri->host = null;
        $uri->query = null;
        $uri->fragment = null;
        return true;
    }
}
