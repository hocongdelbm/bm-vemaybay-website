

<form id="ConfigureSettings" name="ConfigureSettings" enctype='multipart/form-data' method="POST"
      action="index.php?module=Administration&action=BusinessHours&do=save">

<span class='error'>{$error.main}</span>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
    <tr>
        <td>
            {$BUTTONS}
        </td>
    </tr>
</table>

<div class="box-section">
    <h4 class="sub-admin__title">{$MOD.LBL_AOP_BUSINESS_HOURS_SETTINGS}</h4>
<table width="10%" border="0" cellspacing="1" cellpadding="0" class="table-view table-config table-BusinessHours">
    <tbody>
        {foreach from=$DAY_DROPDOWNS key=day item=hours}
    <tr>
        <td width="10%"><span class="label">{$hours.label}</span></td>
        <td width="5%">
            <div class="form-group d-flex gap-2 align-items-center">
                <label for="open_status_{$day}">{$MOD.LBL_BUSINESS_HOURS_OPEN}</label>
                <input data-day="{$day}" type="checkbox" id="open_status_{$day}" name="open_status_{$day}" class="open_check" {if $hours.open_status}checked="checked"{/if}></td>
            </div>
        <td>
         <div id="{$day}_times" class="d-flex align-items-center gap-2">{$MOD.LBL_BUSINESS_HOURS_FROM} <select name="opening_time_{$day}" tabindex="0" id="opening_time_{$day}">{$hours.opening}</select> {$MOD.LBL_BUSINESS_HOURS_TO} <select name="closing_time_{$day}" tabindex="0" id="closing_time_{$day}">{$hours.closing}</select></div>
        </td>
    </tr>
        {/foreach}
    </tbody>
</table>
</div>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
    <tr>
        <td>
            {$BUTTONS}
        </td>
    </tr>
</table>

{$JAVASCRIPT}
</form>
