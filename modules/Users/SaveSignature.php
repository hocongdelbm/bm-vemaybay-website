<?php

require_once('modules/Users/UserSignature.php');
global $current_user;

$us = new UserSignature();
if (isset($_REQUEST['record']) && !empty($_REQUEST['record'])) {
    $us->retrieve($_REQUEST['record']);
} else {
    $us->id = create_guid();
    $us->new_with_id = true;
}

$us->name = $_REQUEST['name'];
$us->signature = strip_tags(br2nl(from_html($_REQUEST['description'])));
$us->signature_html = $_REQUEST['description'];
if (empty($us->user_id) && isset($_REQUEST['the_user_id'])) {
    $us->user_id = $_REQUEST['the_user_id'];
}
$us->save();

$js = '
<script type="text/javascript">
function refreshTemplates() {
	window.opener.refresh_signature_list("'.$us->id.'","'.$us->name.'");
	window.close();
}

refreshTemplates();
window.close();
</script>';

echo $js;
