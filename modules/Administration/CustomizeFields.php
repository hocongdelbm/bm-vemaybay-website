<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;
global $app_list_strings;
global $mod_strings;

global $theme;
global $currentModule;
global $gridline;


echo getClassicModuleTitle('Customize Fields', array('Customize Fields'), false);

?>
<table cellspacing="<?php echo $gridline; ?>" class="other view">
<tr>
<td>
<form>
Module Name:
<select>
<?php
foreach ($moduleList as $module) {
    echo "<option>$module</option>";
}
?>
</select>
<input type="button" value="Edit" />
</form>
</td>
</tr>
</table>

