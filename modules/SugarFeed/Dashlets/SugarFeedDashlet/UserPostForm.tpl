
<form name='form_{$id}' id='form_{$id}'>
<div class="dashletNonTable border-bottom" style='white-space:nowrap;'>
  <table border=0 cellspacing=0 cellpadding=2>
    <tr>
      <td nowrap="nowrap"><span id='more_img_{$id}'>{$more_img}</span><span id='less_img_{$id}' style="display:none;">{$less_img}</span> <b>{$user_name}</b>&nbsp;</td>
      <td style="padding-right: 5px;"><input id="text" name="text" type="text" class="box-input w-100" size='25' maxlength='100' value="" title="{sugar_translate label='LBL_POST_TITLE' module='SugarFeed'} {$user_name} "/></td>
      <td nowrap="nowrap">
      <input type="submit" value="{$LBL_POST}" class="btn btn-primary" onclick="SugarFeed.pushUserFeed('{$id}'); return false;"></td>
        <td>{$facebook}</td>
    </tr>
</table>
<div id='more_{$id}' style='display:none;padding-top:5px'>
  <table>
    <tr>
        <td>{html_options name='link_type' options=$link_types}</td>
        <td><input type='text' name='link_url' title="{sugar_translate label='LBL_URL_LINK_TITLE' module='SugarFeed'}"  size='30'/></td>
    </tr>
  </table>
</div>
</div>

</form>

<form name='SugarFeedReplyForm_{$id}' id='SugarFeedReplyForm_{$id}'>
<input type='hidden' name='parentFeed' value=''>
<div style='white-space:nowrap; display: none;'>
 <table border=0 cellspacing=0 cellpadding=2>
    <tr>
      <td nowrap="nowrap"><b>{$user_name}</b>&nbsp;</td>
      <td style="padding-right: 5px;"><input id="text" name="text" type="text" size='25' maxlength='100' value="" /></td>
      <td nowrap="nowrap">
      <input type="submit" value="{$LBL_POST}" class="button" style="vertical-align:top" onclick="SugarFeed.replyToFeed('{$id}'); return false;"></td>
    </tr>
</table>
</div>
</form>

