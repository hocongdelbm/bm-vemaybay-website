<?php

namespace SuiteCRM;

/**
 * Class HTMLPurifierFilterXmp
 * @package SuiteCRM
 */
class HTMLPurifierFilterXmp extends \HTMLPurifier_Filter
{

    /** @var string $name */
    public $name = 'Xmp';

    /**
     * @param string $html
     * @param \HTMLPurifier_Config $config
     * @param \HTMLPurifier_Context $context
     * @return null|string|string[]
     */
    public function preFilter($html, $config, $context)
    {
        return preg_replace("#<(/)?xmp>#i", "<\\1pre>", $html);
    }
}
