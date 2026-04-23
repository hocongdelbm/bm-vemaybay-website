<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $theme;
global $current_user;


// Create the head of the table.
?>
		<table cellpadding="2" cellspacing="0" border="0">
		
<?php
$current_row=1;
$tracker = BeanFactory::newBean('Trackers');
$history = $tracker->get_recently_viewed($current_user->id);

foreach ($history as $row) {
    $moduleImage = SugarThemeRegistry::current()->getImageURL("{$row['module_name']}.gif");
    echo <<<EOQ
        <tr>
          <td vAlign="top"><IMG width="20" alt="{$row['module_name']}" src="{$moduleImage}" border="0"></td>
          <td noWrap><A  href="index.php?module=$row[module_name]&action=DetailView&record=$row[item_id]">$row[item_summary]</A></td>
        </tr>
EOQ;
}
?>
</table>
