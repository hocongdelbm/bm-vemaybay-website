<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

trait EditAssetsTrait
{
	public function displayCSS()
	{
		$cssVer = inDeveloperMode() ? time() : '1.3.1';

		$css = '<link type="text/css" rel="stylesheet" href="modules/' . $this->bean->module_dir . '/css/view.edit.css?v=' . $cssVer . '" />';

		echo $css;
	}

	public function displayJS()
	{
		$js = '';
		$jsVer   = inDeveloperMode() ? time() : '1.3.1';

		$js .= '<script>
					$(document).ready(function() {
						calculateTotal();
					});
				</script>';

		$js .= '<script src="modules/' . $this->bean->module_dir . '/js/view.edit.js?v=' . $jsVer . '"></script>';
		echo $js;
	}
}
