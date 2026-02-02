<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'custom/entrypoints/entryAuthClass/entryNextCloudPreviewClass.php';
$ep = new entryNextCloudPreviewClass();
$ep->getPublicLinkOCS($_REQUEST);
