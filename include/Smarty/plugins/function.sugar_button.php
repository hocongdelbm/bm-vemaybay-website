<?php
function smarty_function_sugar_button($params, &$smarty)
{
    if (empty($params['module'])) {
        $smarty->trigger_error("sugar_button: missing required param (module)");
    } elseif (empty($params['id'])) {
        $smarty->trigger_error("sugar_button: missing required param (id)");
    } elseif (empty($params['view'])) {
        $smarty->trigger_error("sugar_button: missing required param (view)");
    }

    $js_form = (empty($params['form_id'])) ? "var _form = (this.form) ? this.form : document.forms[0];" : "var _form = document.getElementById('{$params['form_id']}');";

    $type = $params['id'];
    $location = (empty($params['location'])) ? "" : "_".$params['location'];

    $formName = $params['form_id'];

    if (!is_array($type)) {
        $module = $params['module'];
        $view = $params['view'];
        switch (strtoupper($type)) {
            case "SEARCH":
                $output = '<input tabindex="2" title="{$APP.LBL_SEARCH_BUTTON_TITLE}" onclick="SUGAR.savedViews.setChooser();" class="btn btn-primary btn-search" type="submit" name="button" value="{$APP.LBL_SEARCH_BUTTON_LABEL}" id="search_form_submit"/>';
            break;

            case "CANCEL":

                //If the return action is not empty and the return action is detail view and the id is not empty
                $cancelButton  = '{if !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($smarty.request.return_id))}';
                $cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=DetailView&module={$smarty.request.return_module|escape:"url"}&record={$smarty.request.return_id|escape:"url"}\'); return false;" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" type="button" id="'.$type.$location.'"> ';

                //If the return action is not empty and the return action is detail view and the id (from fields) is not empty
                $cancelButton .= '{elseif !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($fields.id.value))}';
                $cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=DetailView&module={$smarty.request.return_module|escape:"url"}&record={$fields.id.value}\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';

                //Bug 1057 If the return action is not empty and the return action is detail view and the id (from both locations) are empty, go to the modules listview
                $cancelButton .= '{elseif !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && empty($fields.id.value)) && empty($smarty.request.return_id)}';
                $cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=ListView&module={$smarty.request.return_module|escape:"url"}&record={$fields.id.value}\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';


                //Bug 893 if the return action is not empty and the return module is not empty, go back to that page
                $cancelButton .= '{elseif !empty($smarty.request.return_action) && !empty($smarty.request.return_module)}';
                $cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action={$smarty.request.return_action}&module={$smarty.request.return_module|escape:"url"}\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';

                //If the return action is empty but the return id is in fields
                $cancelButton .= '{elseif empty($smarty.request.return_action) || empty($smarty.request.return_id) && !empty($fields.id.value)}';
                $cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=index&module='.$module.'\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';



                $cancelButton .= '{else}';
                $cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=index&module={$smarty.request.return_module|escape:"url"}&record={$smarty.request.return_id|escape:"url"}\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';
                $cancelButton .= '{/if}';


                //$cancelButton = '{$smarty.request.return_action}'.'{$smarty.request.return_module}';


                $output = $cancelButton;
            break;

            case "DELETE":
                $output = '{if $bean->aclAccess("delete")}<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="btn btn-delete btn-danger" onclick="'.$js_form.' _form.return_module.value=\'' . $module . '\'; _form.return_action.value=\'ListView\'; _form.action.value=\'Delete\'; if(confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\')) SUGAR.ajaxUI.submitForm(_form); return false;" type="submit" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}" id="delete_button">{/if} ';
            break;

            case "DUPLICATE":
                $output = '{if $bean->aclAccess("edit")}<input title="{$APP.LBL_DUPLICATE_BUTTON_TITLE}" accessKey="{$APP.LBL_DUPLICATE_BUTTON_KEY}" class="btn btn-duplicate btn-secondary" onclick="'.$js_form.' _form.return_module.value=\''. $module . '\'; _form.return_action.value=\'DetailView\'; _form.isDuplicate.value=true; _form.action.value=\'' . $view . '\'; _form.return_id.value=\'{$id}\';SUGAR.ajaxUI.submitForm(_form);" type="button" name="Duplicate" value="{$APP.LBL_DUPLICATE_BUTTON_LABEL}" id="duplicate_button">{/if} ';
            break;

            case "EDIT":
                $output = '{if $bean->aclAccess("edit")}<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="btn btn-primary" onclick="'.$js_form.' _form.return_module.value=\'' . $module . '\'; _form.return_action.value=\'DetailView\'; _form.return_id.value=\'{$id}\'; _form.action.value=\'EditView\';SUGAR.ajaxUI.submitForm(_form);" type="button" name="Edit" id="edit_button" value="{$APP.LBL_EDIT_BUTTON_LABEL}">{/if} ';
            break;

            case "FIND_DUPLICATES":
                $output = '{if $bean->aclAccess("edit") && $bean->aclAccess("delete")}<input title="{$APP.LBL_DUP_MERGE}" class="btn btn-duplicate btn-secondary" onclick="'.$js_form.' _form.return_module.value=\'' . $module . '\'; _form.return_action.value=\'DetailView\'; _form.return_id.value=\'{$id}\'; _form.action.value=\'Step1\'; _form.module.value=\'MergeRecords\';SUGAR.ajaxUI.submitForm(_form);" type="button" name="Merge" value="{$APP.LBL_DUP_MERGE}" id="merge_duplicate_button">{/if} ';
            break;

            case "SAVE":
                $view = ($_REQUEST['action'] == 'EditView') ? 'EditView' : (($view == 'EditView') ? 'EditView' : $view);

                // $output = '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-save" onclick="'.$js_form.' {if $isDuplicate}_form.return_id.value=\'\'; {/if}_form.action.value=\'Save\'; if(check_form(\'' . $formName . '\'))SUGAR.ajaxUI.submitForm(_form);return false;" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" id="'.$type.$location.'">{/if} ';
                $output = '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-save" onclick="this.form.action.value=\'Save\'; return check_form(\'' . $formName . '\');" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" id="'.$type.$location.'">{/if} ';
                
            break;

            case "SUBPANELSAVE":
                if ($view == 'QuickCreate' || (isset($_REQUEST['target_action']) && strtolower($_REQUEST['target_action']) == 'quickcreate')) {
                    $view =  "form_SubpanelQuickCreate_{$module}";
                }

                $output = '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}"  class="btn btn-save" onclick="'.$js_form.' disableOnUnloadEditView(); _form.action.value=\'Save\';if(check_form(\''.$view.'\'))return SUGAR.subpanelUtils.inlineSave(_form.id, \'' . $params['module'] . '_subpanel_save_button\');return false;" type="submit" name="' . $params['module'] . '_subpanel_save_button" id="' . $params['module'] . '_subpanel_save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if} ';

            break;
            case "SUBPANELCANCEL":
                $output = '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" class="btn btn-danger" onclick="return SUGAR.subpanelUtils.cancelCreate($(this).attr(\'id\'));return false;" type="submit" name="' . $params['module'] . '_subpanel_cancel_button" id="' . $params['module'] . '_subpanel_cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> ';

            break;
            case "SUBPANELFULLFORM":
                $output = '<input title="{$APP.LBL_FULL_FORM_BUTTON_TITLE}" class="btn btn-warning" onclick="'.$js_form.' disableOnUnloadEditView(_form); _form.return_action.value=\'DetailView\'; _form.action.value=\'EditView\'; if(typeof(_form.to_pdf)!=\'undefined\') _form.to_pdf.value=\'0\';" type="submit" name="' . $params['module'] . '_subpanel_full_form_button" id="' . $params['module'] . '_subpanel_full_form_button" value="{$APP.LBL_FULL_FORM_BUTTON_LABEL}"> ';
                $output .= '<input type="hidden" name="full_form" value="full_form">';
            break;
            case "DCMENUCANCEL":
                $output = '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="javascript:lastLoadedMenu=undefined;DCMenu.closeOverlay();return false;" type="submit" name="' . $params['module'] . '_dcmenu_cancel_button" id="' . $params['module'] . '_dcmenu_cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> ';
            break;
            case "DCMENUSAVE":
                            if ($view == 'QuickCreate') {
                                $view = "form_DCQuickCreate_{$module}";
                            } elseif ($view == 'EditView') {
                                $view = "form_DCEditView_{$module}";
                            }
                $output = '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-save" onclick="'.$js_form.' _form.action.value=\'Save\';if(check_form(\''.$view.'\'))return DCMenu.save(_form.id, \'' . $params['module'] . '_subpanel_save_button\');return false;" type="submit" name="' . $params['module'] . '_dcmenu_save_button" id="' . $params['module'] . '_dcmenu_save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if} ';
            break;
            case "DCMENUFULLFORM":
                $output = '<input title="{$APP.LBL_FULL_FORM_BUTTON_TITLE}" accessKey="{$APP.LBL_FULL_FORM_BUTTON_KEY}" class="btn btn-primary" onclick="'.$js_form.' disableOnUnloadEditView(_form); _form.return_action.value=\'DetailView\'; _form.action.value=\'EditView\'; _form.return_module.value=\'' . $params['module'] . '\';_form.return_id.value=_form.record.value;if(typeof(_form.to_pdf)!=\'undefined\') _form.to_pdf.value=\'0\';SUGAR.ajaxUI.submitForm(_form,null,true);DCMenu.closeOverlay();" type="button" name="' . $params['module'] . '_subpanel_full_form_button" id="' . $params['module'] . '_subpanel_full_form_button" value="{$APP.LBL_FULL_FORM_BUTTON_LABEL}"> ';
                $output .= '<input type="hidden" name="full_form" value="full_form">';
                $output .= '<input type="hidden" name="is_admin" value="">';
            break;
            case "POPUPSAVE":
                $view = ($view == 'QuickCreate') ? "form_QuickCreate_{$module}" : $view;
                $output = '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" '
                     . 'class="btn btn-save" onclick="'.$js_form.' _form.action.value=\'Popup\';'
                     . 'return check_form(\''.$view.'\')" type="submit" name="' . $params['module']
                     . '_popupcreate_save_button" id="' . $params['module']
                     . '_popupcreate_save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if} ';
            break;
            case "POPUPCANCEL":
                $output = '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" '
                     . 'class="btn btn-danger" onclick="toggleDisplay(\'addform\');return false;" '
                     . 'name="' . $params['module'] . '_popup_cancel_button" type="submit"'
                     . 'id="' . $params['module'] . '_popup_cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> ';
            break;
            case "AUDIT":
                $popup_request_data = array(
                    'call_back_function' => 'set_return',
                    'form_name' => 'EditView',
                    'field_to_name_array' => array(),
                );
                $json = getJSONobj();

                require_once('include/SugarFields/Parsers/MetaParser.php');
                $encoded_popup_request_data = MetaParser::parseDelimiters($json->encode($popup_request_data));
                $audit_link = '<input id="btn_view_change_log" title="{$APP.LNK_VIEW_CHANGE_LOG}" class="btn btn-secondary" onclick=\'open_popup("Audit", "600", "400", "&record={$fields.id.value}&module_name=' . $params['module'] . '", true, false, ' . $encoded_popup_request_data . '); return false;\' type="button" value="{$APP.LNK_VIEW_CHANGE_LOG}">';
                $output = '{if $bean->aclAccess("detail")}{if !empty($fields.id.value) && $isAuditEnabled}'.$audit_link.'{/if}{/if}';
            break;


      } //switch
        if (isset($params['appendTo'])) {
            $smarty->append($params['appendTo'], $output);
            return;
        }
        return $output;
    } elseif (is_array($type) && isset($type['sugar_html'])) {
        require_once('include/SugarHtml/SugarHtml.php');

        $dom_tree = SugarHtml::parseSugarHtml($type['sugar_html']);
        replaceFormClick($dom_tree, $js_form);
        $output = SugarHtml::createHtml($dom_tree);

        if (isset($params['appendTo'])) {
            $smarty->append($params['appendTo'], $output);
            return;
        }
        return $output;
    } elseif (is_array($type) && isset($type['customCode'])) {
        require_once('include/SugarHtml/SugarHtml.php');

        $dom_tree = SugarHtml::parseHtmlTag($type['customCode']);
        $hidden_exists = false;

        replaceFormClick($dom_tree, $js_form, $hidden_exists);
        if ($hidden_exists) {
            //If the customCode contains hidden fields, the extracted hidden fields need to append in the original form
            $form = $smarty->get_template_vars('form');
            $hidden_fields = $dom_tree;
            extractHiddenInputs($hidden_fields);
            if (!isset($form)) {
                $form = array();
            }
            if (!isset($form['hidden'])) {
                $form['hidden'] = array();
            }
            $form['hidden'][] = SugarHtml::createHtml($hidden_fields);
            $smarty->assign('form', $form);
        }
        $output = SugarHtml::createHtml($dom_tree);

        if (isset($params['appendTo'])) {
            $smarty->append($params['appendTo'], $output);
            return;
        }
        return $output;
    }
}
/**
 * Bug#51862: Reproduce the JS onclick for upgraded instances
 *
 * @param array $dom_tree - Cascade array form generated by SugarHtml::parseHtmlTag
 * @param string $js_form - JS getter to assign _form object by ID
 * @param bool $hidden_field_exists - whether the selected element contains hidden fields or not
 * @return array - two boolean variables.
 *                 $set_submit - whether the replace operation is excuted or not
 *                 $is_hidden_field - where current attributes contains the key "hidden" or not
 */
function replaceFormClick(&$dom_tree = array(), $js_form = '', &$hidden_field_exists = false)
{
    $set_submit = false;
    $is_hidden_field = false;
    //if the code is wrapped with the form element, it will escape the operation for JS replacement
    if (isset($dom_tree['tag']) && $dom_tree['tag'] == 'form') {
        return false;
    }

    if (isset($dom_tree['type']) && $dom_tree['type'] == 'hidden') {
        $is_hidden_field = true;
    }

    //Replace the JS syntax where the sugar_button contains the event handler for this.form
    if (isset($dom_tree['onclick'])) {
        if (strpos($dom_tree['onclick'], "this.form") !== false) {
            $dom_tree['onclick'] = str_replace("this.form", "_form", $dom_tree['onclick']);
            if (substr($dom_tree['onclick'], -1) != ';') {
                $dom_tree['onclick'] .= ";";
            }
            //Onclick handler contains returning a variable, for example it prompts a confirm message.
            if (strpos($dom_tree['onclick'], "return ") !== false) {
                $dom_tree['onclick'] = $js_form.' var _onclick=(function(){ldelim}'.$dom_tree['onclick']."{rdelim}()); if(_onclick!==false) _form.submit();";
            } else {
                $dom_tree['onclick'] = $js_form.$dom_tree['onclick']."_form.submit();";
            }

            $set_submit = true;
        }
    }
    foreach ($dom_tree as $key => $sub_tree) {
        if (is_array($sub_tree)) {
            list($_submit, $_hidden) = replaceFormClick($dom_tree[$key], $js_form, $hidden_field_exists);
            $set_submit = ($set_submit) ? $set_submit : $_submit;
            $is_hidden_field = ($is_hidden_field) ? $is_hidden_field : $_hidden;
        }
    }

    if ($set_submit && isset($dom_tree['type'])) {
        $dom_tree['type'] = "button";
        $set_submit = false;
    }
    if ($is_hidden_field && isset($dom_tree['tag']) && $dom_tree['tag'] == 'input') {
        $hidden_field_exists = true;
        $is_hidden_field = false;
    }

    return array($set_submit, $is_hidden_field);
}

/**
 * Bug#51862: Extract hidden field form the original dom structure
 * @param array $dom_tree - Cascade array form generated by SugarHtml::parseHtmlTag
 */
function extractHiddenInputs(&$dom_tree = array())
{
    $allow_types = array(
        'hidden'
    );
    //all hidden fields in the form elements must NOT attach in the original form
    if (isset($dom_tree['tag']) && $dom_tree['tag'] == 'form') {
        $dom_tree = array();
    }
    foreach ($dom_tree as $key => $sub_tree) {
        if (is_numeric($key) && isset($sub_tree['tag']) && $sub_tree['tag'] == 'input') {
            if (!isset($sub_tree['type']) || in_array($sub_tree['type'], $allow_types) === false) {
                unset($dom_tree[$key]);
            }
        } elseif (is_array($sub_tree)) {
            extractHiddenInputs($dom_tree[$key]);
        }
    }
    if (isset($dom_tree['tag']) && $dom_tree['tag'] == 'input') {
        if (!isset($dom_tree['type']) || in_array($dom_tree['type'], $allow_types) === false) {
            $dom_tree = array();
        }
    }
}
