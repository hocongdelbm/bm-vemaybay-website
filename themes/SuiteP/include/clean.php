<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// classes where moved that we have one class per file
// this file has been kept for backward compatibility
// Remap old namespace to the SuiteCRM namespace
// Older code can still use this the old class names
// new code can leverage the autoloader and use the SuiteCRM namespace
class HTMLPurifier_URIScheme_cid extends \SuiteCRM\HTMLPurifierURISchemeCid {}
class HTMLPurifier_Filter_Xmp extends \SuiteCRM\HTMLPurifierFilterXmp {}
class SugarCleaner extends \SuiteCRM\HtmlSanitizer {}
class SugarURIFilter extends \SuiteCRM\URIFilter {}
