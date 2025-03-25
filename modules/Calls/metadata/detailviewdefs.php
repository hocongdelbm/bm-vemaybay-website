<?php
global $current_user;

$viewdefs['Calls'] = array(
	'DetailView' => array(
		'templateMeta' => array(
			'form' => array(
				'buttons' => array(
					'EDIT',
					// 'DUPLICATE',
					'DELETE',
					// array(
					// 	'customCode' => '{if $bean->aclAccess("delete") && $current_user->is_admin == 1}<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="btn btn-delete btn-danger" onclick="this.form.return_module.value=\'Calls\'; this.form.return_action.value=\'EditView\'; this.form.return_id.value=\'{$return_id}\'; this.form.action.value=\'Delete\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');" type="submit" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}">{/if}',
					// 	'sugar_html' => array(
					// 	  'type' => 'submit',
					// 	  'value' => '{$APP.LBL_DELETE_BUTTON_LABEL}',
					// 	  'htmlOptions' => array(
					// 	    'title' => '{$APP.LBL_DELETE_BUTTON_TITLE}',
					// 	    'accessKey' => '{$APP.LBL_DELETE_BUTTON_KEY}',
					// 	    'class' => 'btn btn-delete btn-danger',
					// 	    'onclick' => 'this.form.return_module.value=\'Calls\'; this.form.return_action.value=\'ListView\'; this.form.return_id.value=\'{$return_id}\'; this.form.action.value=\'Delete\'; return confirm(\'Bạn chắc muốn xoá hoàn toàn cuộc gọi này?\');',
					// 	    'name' => 'Delete',
					// 	  ),
					// 	  'template' => '{if $bean->aclAccess("delete") && $current_user->is_admin == 1}[CONTENT]{/if}',
					// 	),
					// ),
					array('customCode' => '{$CALLS_STATUS}'),
					array('customCode' => '{$CHANGE_STATUS}'),
					array('customCode' => '{$CALLS_ANNOTATION}'),
					array('customCode' => '{$REPORT_BUG}'),
					// array(
					// 	'customCode' => '{if $fields.status.value != "Held" && $bean->aclAccess("edit")} <input type="hidden" name="isSaveAndNew" value="false">  <input type="hidden" name="status" value="">  <input type="hidden" name="isSaveFromDetailView" value="true">  <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"   class="btn btn-secondary"  onclick="this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Calls\';this.form.isDuplicate.value=true;this.form.isSaveAndNew.value=true;this.form.return_action.value=\'EditView\'; this.form.return_id.value=\'{$fields.id.value}\'" id="close_create_button" name="button"  value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
					// 	'sugar_html' => array(
					// 		'type' => 'submit',
					// 		'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
					// 		'htmlOptions' => array(
					// 			'title' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
					// 			'class' => 'btn btn-warning',
					// 			'onclick' => 'this.form.isSaveFromDetailView.value=true; this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Calls\';this.form.isDuplicate.value=true;this.form.isSaveAndNew.value=true;this.form.return_action.value=\'EditView\'; this.form.return_id.value=\'{$fields.id.value}\'',
					// 			'name' => 'button',
					// 			'id' => 'close_create_button',
					// 		),
					// 		'template' => '{if $fields.status.value != "Held" && $bean->aclAccess("edit")}[CONTENT]{/if}',
					// 	),
					// ),
					// array(
					// 	'customCode' => '{if $fields.status.value != "Held" && $bean->aclAccess("edit")} <input type="hidden" name="isSave" value="false">  <input title="{$APP.LBL_CLOSE_BUTTON_TITLE}"  accesskey="{$APP.LBL_CLOSE_BUTTON_KEY}"  class="btn btn-warning"  onclick="this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Calls\';this.form.isSave.value=true;this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'" id="close_button" name="button1"  value="{$APP.LBL_CLOSE_BUTTON_TITLE}"  type="submit">{/if}',
					// 	'sugar_html' => array(
					// 		'type' => 'submit',
					// 		'value' => '{$APP.LBL_CLOSE_BUTTON_TITLE}',
					// 		'htmlOptions' => array(
					// 			'title' => '{$APP.LBL_CLOSE_BUTTON_TITLE}',
					// 			'accesskey' => '{$APP.LBL_CLOSE_BUTTON_KEY}',
					// 			'class' => 'btn btn-danger',
					// 			'onclick' => 'this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Calls\';this.form.isSave.value=true;this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\';this.form.isSaveFromDetailView.value=true',
					// 			'name' => 'button1',
					// 			'id' => 'close_button',
					// 		),
					// 		'template' => '{if $fields.status.value != "Held" && $bean->aclAccess("edit")}[CONTENT]{/if}',
					// 	),
					// ),
					// 'SA_RESCHEDULE' => array(
					// 	'customCode' => '{if $fields.status.value != "Held"} <input title="{$MOD.LBL_RESCHEDULE}" class="btn btn-secondary" onclick="get_form();" name="Reschedule" id="reschedule_button" value="{$MOD.LBL_RESCHEDULE}" type="button">{/if}',
					// ),
				),
				'hidden' => array(
					'<input type="hidden" name="isSaveAndNew">',
					'<input type="hidden" name="status">',
					'<input type="hidden" name="isSaveFromDetailView">',
					'<input type="hidden" name="isSave">',
				),
				'headerTpl' => 'modules/Calls/tpls/detailHeader.tpl',
			),
			'maxColumns' => '2',
			'widths' => array(
				array('label' => '10', 'field' => '30'),
				array('label' => '10', 'field' => '30'),
			),
			'useTabs' => true,
			'includes' => array(
				// 'SA_RESCHEDULE' => array(
				// 	'file' => 'modules/Calls_Reschedule/reschedule_form.js',
				// ),
				// array('file' => 'modules/Reminders/Reminders.js'),
				array('file' => 'modules/Calls/js/view.detail.js'),
			),
			// 'tabDefs' =>
			// array(
			//   'LBL_CALL_INFORMATION' =>
			//   array(
			//     'newTab' => true,
			//     'panelDefault' => 'expanded',
			//   ),
			//   'LBL_RESCHEDULE_PANEL' =>
			//   array(
			//     'newTab' => true,
			//     'panelDefault' => 'expanded',
			//   ),
			//   'LBL_PANEL_ASSIGNMENT' =>
			//   array(
			//     'newTab' => true,
			//     'panelDefault' => 'expanded',
			//   ),
			// ),
		),
		'panels' => array(
			'lbl_call_information' => array(
				array(
					array(
						'name' => 'name',
						'label' => 'LBL_SUBJECT',
					),
					array(
						'name' => 'parent_name',
						'customLabel' => '{sugar_translate label=\'LBL_LIST_CONTACT\' module=$fields.parent_type.value}',
					),
					// array(
					//   'name' => 'direction',
					//   'customCode' => '{$fields.direction.options[$fields.direction.value]} {$fields.status.options[$fields.status.value]}',
					//   'label' => 'LBL_STATUS',
					// ),
				),
				array(
					array(
						'name' => 'call_from',
						'label' => 'LBL_CALL_FROM',
					),
					array(
						'name' => 'call_to',
						'label' => 'LBL_CALL_TO',
					),
				),
				array(
					array(
						'name' => 'other_caller',
						'label' => 'LBL_OTHER_CALLER',
					),
					array(
						'name' => 'assigned_user_name',
						'customCode' => '{$fields.assigned_user_name.value}',
						'label' => 'LBL_ASSIGNED_TO',
					),
				),
				array(
					array(
						'name' => 'direction',
						'label' => 'LBL_DIRECTION',
					),
					array(
						'name' => 'call_type',
						'label' => 'LBL_CALL_TYPE',
					),
				),
				array(
					array(
						'name' => 'status',
						'label' => 'LBL_STATUS',
					),
					array(
						'name' => 'record_file',
						'label' => 'LBL_RECORD_FILE',
						'customCode' => '<audio controls style="height: 40px;"><source src="{$fields.record_file.value}" type="audio/wav"></audio>',
					)
				),
				array(
					array(
						'name' => 'date_start',
						'customCode' => '{$fields.date_start.value} {$fields.time_start.value}&nbsp;',
						'label' => 'LBL_DATE_TIME',
					),
					array(
                        'name' => 'date_wait',
                        'customCode' => '{$CUS_DATE_WAIT}',
                        'label' => 'LBL_DATE_WAIT',
                    ),
				),
				array(
                    array(
                        'name' => 'date_accept',
                        'customCode' => '{$CUS_DATE_ACCEPT}',
                        'label' => 'LBL_DATE_ACCEPT',
                    ),
					array(
                        'name' => 'call_talk',
                        'label' => 'LBL_CALL_TALK',
                        'customCode' => '{$CUS_CALL_TALK}',
                    ),
                ),
				array(
					array(
                        'name' => 'date_end',
                        'customCode' => '{$fields.date_end.value} {$fields.time_end.value}&nbsp;',
                        'label' => 'LBL_DATE_END_TIME',
                    ),
                	array(
                        'name' => 'call_duration',
                        'label' => 'LBL_CALL_DURATION',
                        'customCode' => '{$CUS_CALL_DURATION}',
                    ),
                ),
				// array(
				// array(
				//   'name' => 'duration_hours',
				//   'customCode' => '{$fields.duration_hours.value}{$MOD.LBL_HOURS_ABBREV} {$fields.duration_minutes.value}{$MOD.LBL_MINSS_ABBREV}&nbsp;',
				//   'label' => 'LBL_DURATION',
				// ),
				//          1 =>
				//          array (
				//            'name' => 'reminder_time',
				//            'customCode' => '{include file="modules/Meetings/tpls/reminders.tpl"}',
				//            'label' => 'LBL_REMINDER',
				//          ),
				// array(
				//   'name' => 'reminders',
				//   'label' => 'LBL_REMINDERS',
				// ),
				// array()
				// ),
				array(
					array(
						'name' => 'booking',
						'studio' => 'visible',
						'label' => 'LBL_BOOKING',
					),
					array(
						'name' => 'call_id',
						'label' => 'LBL_CALL_ID',
					),
				),
				array(
					array(
						'name' => 'call_sources',
						'customCode' => '{$CUSTOM_CALL_SOURCES}',
						'label' => 'LBL_CALL_SOURCES',
					),
					array(
						'name' => 'type_call_sources',
						'label' => 'LBL_TYPE_CALL_SOURCES',
					),
				),
				array(
					array(
						'name' => 'description',
						'comment' => 'Full text of the note',
						'label' => 'LBL_DESCRIPTION',
					),
					array(
						'name' => 'hangup_cause',
						'comment' => 'Nguyên nhân ngắt máy',
						'label' => 'LBL_HANGUP_CAUSE',
					),
				),
				array(
					array(
						'name' => 'call_reason',
						'comment' => 'Phân loại cuộc gọi. Nhu cầu khách hàng',
						'label' => 'LBL_CALL_REASON',
					),
					array(
						'name' => 'is_success',
						'label' => 'LBL_IS_SUCCESS',
						'customCode' => '{$CUS_IS_SUCCESS}',
					),
				),
				array(
					array(
						'name' => 'call_failed_cause',
						'label' => 'LBL_CALL_FAILED_CAUSE',
						'customCode' => '{$CUS_CALL_FAILED_CAUSE}',
					),
					array(
						'name' => 'call_mos',
						'label' => 'LBL_CALL_MOS',
						'customCode' => '{$CUS_CALL_MOS}',
					),
				),
				array(
					array(
						'name' => 'date_entered',
						'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;',
						'label' => 'LBL_DATE_ENTERED',
					),
					array(
						'name' => 'date_modified',
						'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}&nbsp;',
						'label' => 'LBL_DATE_MODIFIED',
					),
				),
				// array(
				//   array(
				//     'name' => 'reschedule_history',
				//     'comment' => 'Call duration, minutes portion',
				//     'label' => 'LBL_RESCHEDULE_HISTORY',
				//   ),
				//   array()
				// )
			),
		),
	),
);
