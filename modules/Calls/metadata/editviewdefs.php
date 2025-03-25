<?php
$viewdefs['Calls'] = array(
	'EditView' => array(
		'templateMeta' => array(
			'includes' => array(
				array('file' => 'modules/Reminders/Reminders.js'),
			),
			'maxColumns' => '2',
			'form' => array(
				'hidden' => array(
					'<input type="hidden" name="isSaveAndNew" value="false">',
					'<input type="hidden" name="send_invites">',
					'<input type="hidden" name="user_invitees">',
					'<input type="hidden" name="lead_invitees">',
					'<input type="hidden" name="contact_invitees">',
				),
				// 'buttons' => array(
				// 	array(
				// 		'customCode' => '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id="SAVE_HEADER" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary" onclick="SUGAR.calls.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if}formSubmitCheck();;" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
				// 	),
				// 	'CANCEL',
				// 	array(
				// 		'customCode' => '<input title="{$MOD.LBL_SEND_BUTTON_TITLE}" id="SAVE_SEND_HEADER" class="btn btn-success" onclick="document.EditView.send_invites.value=\'1\';SUGAR.calls.fill_invitees();document.EditView.action.value=\'Save\';document.EditView.return_action.value=\'EditView\';document.EditView.return_module.value=\'{$smarty.request.return_module}\';formSubmitCheck();;" type="button" name="button" value="{$MOD.LBL_SEND_BUTTON_LABEL}">',
				// 	),
				// 	array(
				// 		'customCode' => '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" id="CLOSE_CREATE_HEADER" accessKey="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_KEY}" class="btn btn-secondary" onclick="SUGAR.calls.fill_invitees(); document.EditView.status.value=\'Held\'; document.EditView.action.value=\'Save\'; document.EditView.return_module.value=\'Calls\'; document.EditView.isDuplicate.value=true; document.EditView.isSaveAndNew.value=true; document.EditView.return_action.value=\'EditView\'; document.EditView.return_id.value=\'{$fields.id.value}\'; formSubmitCheck();" type="button" name="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_LABEL}">{/if}',
				// 	),
				// ),
				// 'buttons_footer' => array(
				// 	array(
				// 		'customCode' => '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id="SAVE_FOOTER" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary" onclick="SUGAR.calls.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
				// 	),
				// 	'CANCEL',
				// 	array(
				// 		'customCode' => '<input title="{$MOD.LBL_SEND_BUTTON_TITLE}" id="SAVE_SEND_FOOTER" class="btn btn-success" onclick="document.EditView.send_invites.value=\'1\';SUGAR.calls.fill_invitees();document.EditView.action.value=\'Save\';document.EditView.return_action.value=\'EditView\';document.EditView.return_module.value=\'{$smarty.request.return_module}\';formSubmitCheck();;" type="button" name="button" value="{$MOD.LBL_SEND_BUTTON_LABEL}">',
				// 	),
				// 	array(
				// 		'customCode' => '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" id="CLOSE_CREATE_FOOTER" accessKey="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_KEY}" class="btn btn-secondary" onclick="SUGAR.calls.fill_invitees(); document.EditView.status.value=\'Held\'; document.EditView.action.value=\'Save\'; document.EditView.return_module.value=\'Calls\'; document.EditView.isDuplicate.value=true; document.EditView.isSaveAndNew.value=true; document.EditView.return_action.value=\'EditView\'; document.EditView.return_id.value=\'{$fields.id.value}\'; formSubmitCheck();" type="button" name="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_LABEL}">{/if}',
				// 	),
				// ),
				'headerTpl' => 'modules/Calls/tpls/header.tpl',
				// 'buttons_footer' => array(
				// 	array(
				// 		'customCode' => '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id="SAVE_FOOTER" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary" onclick="SUGAR.calls.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
				// 	),
				// 	'CANCEL',
				// 	array(
				// 		'customCode' => '<input title="{$MOD.LBL_SEND_BUTTON_TITLE}" id="save_and_send_invites_footer" class="btn btn-secondary" onclick="document.EditView.send_invites.value=\'1\';SUGAR.calls.fill_invitees();document.EditView.action.value=\'Save\';document.EditView.return_action.value=\'EditView\';document.EditView.return_module.value=\'{$smarty.request.return_module}\'; formSubmitCheck();"type="button" name="button" value="{$MOD.LBL_SEND_BUTTON_LABEL}">',
				// 	),
				// 	array(
				// 		'customCode' => '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" id="close_and_create_new_footer" class="btn btn-secondary" onclick="SUGAR.calls.fill_invitees(); document.EditView.status.value=\'Held\'; document.EditView.action.value=\'Save\'; document.EditView.return_module.value=\'Meetings\'; document.EditView.isDuplicate.value=true; document.EditView.isSaveAndNew.value=true; document.EditView.return_action.value=\'EditView\'; document.EditView.return_id.value=\'{$fields.id.value}\'; formSubmitCheck();"type="button" name="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_LABEL}">{/if}',
				// 	),
				// ),
				// 'footerTpl' => 'modules/Calls/tpls/footer.tpl',
			),
			'widths' => array(
				array('label' => '10', 'field' => '30'),
				array('label' => '10', 'field' => '30'),
			),
			'javascript' => '{sugar_getscript file="cache/include/javascript/sugar_grp_jsolait.js"}
				<script type="text/javascript">{$JSON_CONFIG_JAVASCRIPT}</script>
				<script>toggle_portal_flag();function toggle_portal_flag()  {ldelim} {$TOGGLE_JS} {rdelim}
				function formSubmitCheck(){ldelim}var duration=true;if(typeof(isValidDuration)!="undefined"){ldelim}duration=isValidDuration();{rdelim}if(check_form(\'EditView\') && duration){ldelim}SUGAR.ajaxUI.submitForm("EditView");{rdelim}{rdelim}</script>
			',
			'useTabs' => false,
			'tabDefs' => array(
				'LBL_CALL_INFORMATION' => array(
					'newTab' => false,
					'panelDefault' => 'expanded',
				),
			),
		),
		'panels' => array(
			'lbl_call_information' => array(
				// array(
				// array(
				// 	'name' => 'direction',
				// 	'label' => 'LBL_LIST_DIRECTION',
				// ),
				// array(
				// 	'name' => 'parent_name',
				// 	'label' => 'LBL_LIST_RELATED_TO',
				// ),
				// ),

				// array(
				// 	array(
				// 		'name' => 'call_from',
				// 		'label' => 'LBL_CALL_FROM',
				// 	),
				// 	array(
				// 		'name' => 'call_to',
				// 		'label' => 'LBL_CALL_TO',
				// 	),
				// ),

				// array(
				// 	array(
				// 		'name' => 'date_start',
				// 		'label' => 'LBL_DATE_TIME',
				// 		'displayParams' => array(
				// 			'updateCallback' => 'SugarWidgetScheduler.update_time();',
				// 		),
				// 	),
				// 	array(
				// 		'name' => 'date_end',
				// 		'label' => 'LBL_DATE_END',
				// 		'displayParams' => array(
				// 			'updateCallback' => 'SugarWidgetScheduler.update_time();',
				// 		),
				// 	),
				// ),

				// array(
				// 	array(
				// 	    'name' => 'status',
				// 	    'label' => 'LBL_STATUS',
				// 	),
				// 	array(
				// 	)
				//  ),
				array(
					array(
						'name' => 'booking',
						'studio' => 'visible',
						'label' => 'LBL_BOOKING',
					),
					array(
						'name' => 'parent_name',
						'label' => 'LBL_LIST_RELATED_TO',
					)
				),
				array(
					array(
						'name' => 'call_reason',
						'label' => 'LBL_CALL_REASON',
					),
					array(
					)
				),
				array(
					array(
						'name' => 'description',
						'comment' => 'Full text of the note',
						'label' => 'LBL_DESCRIPTION',
					),
					array(
						'name' => 'assigned_user_name',
						'label' => 'LBL_ASSIGNED_TO',
					),
				),

				// array(
				// 	array(
				// 		'name' => 'reminders',
				// 		'customCode' => '{include file="modules/Reminders/tpls/reminders.tpl"}',
				// 		'label' => 'LBL_REMINDERS',
				// 	),
				//     array(
				//         'name' => 'calls_status',
				//         'label' => 'LBL_CALLS_STATUS',
				//     )
				// ),
			),
		),
	),
);
