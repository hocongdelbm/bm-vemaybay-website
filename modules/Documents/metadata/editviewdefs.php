<?php
$viewdefs['Documents'] =
  array(
    'EditView' =>
    array(
      'templateMeta' =>
      array(
        'form' =>
        array(
          'enctype' => 'multipart/form-data',
          'hidden' =>
          array(
            0 => '<input type="hidden" name="old_id" value="{$fields.document_revision_id.value}">',
            1 => '<input type="hidden" name="contract_id" value="{$smarty.request.contract_id}">',
          ),
        ),
        'maxColumns' => '2',
        'widths' =>
        array(
          0 =>
          array(
            'label' => '10',
            'field' => '30',
          ),
          1 =>
          array(
            'label' => '10',
            'field' => '30',
          ),
        ),
        'javascript' => '{sugar_getscript file="include/javascript/popup_parent_helper.js"}
{sugar_getscript file="cache/include/javascript/sugar_grp_jsolait.js"}
{sugar_getscript file="modules/Documents/documents.js"}
{sugar_getscript file="modules/Documents/js/booking_popup.js"}
{sugar_getscript file="modules/Documents/js/preview_handler.js"}

',
        'useTabs' => false,
        'tabDefs' =>
        array(
          'LBL_DOCUMENT_INFORMATION' =>
          array(
            'newTab' => false,
            'panelDefault' => 'expanded',
          ),
        ),
      ),
      'panels' =>
      array(
        'lbl_document_information' =>
        array(
          array(
            array(
              'name' => 'filename',
              'displayParams' =>
              array(
                'onchangeSetFileNameTo' => 'document_name',
              ),
            ),
            array(
              'name' => 'status_id',
              'label' => 'LBL_DOC_STATUS',
            ),
          ),
          array(
            0 =>
            array(
              'name' => 'preview_image',
              'label' => 'LBL_PREVIEW_PHOTO',
              'customCode' => '<div id="file-preview-container" style="margin-top:10px; padding:10px; border:1px solid #ddd;">' .
                '<img id="file-preview-image" src="{$PREVIEW_IMAGE_URL}" style="max-width:300px; max-height:300px; {if !$HAS_PREVIEW_IMAGE}display:none;{/if}"/>' .
                '<div id="file-preview-text" style="color: #999; {if $HAS_PREVIEW_IMAGE}display:none;{/if}">Chưa chọn file</div>' .
                '</div>',
            ),
          ),
          array(
            0 => 'document_name',
            1 =>
            array(
              'name' => 'revision',
              'customCode' => '<input name="revision" type="text" value="{$fields.revision.value}" {$DISABLED}>',
            ),
          ),
          array(
            array(
              'name' => 'template_type',
              'label' => 'LBL_DET_TEMPLATE_TYPE',
            ),
            array(
              'name' => 'is_template',
              'label' => 'LBL_DET_IS_TEMPLATE',
            ),
          ),
          array(
            array(
              'name' => 'active_date',
            ),
            'exp_date',
          ),
          array(
            0 => 'category_id',
            1 => 'subcategory_id',
          ),
          array(
            array(
              'name' => 'description',
            ),
          ),
          array(
            array(
              'name' => 'booking_id',
              'label' => 'LBL_BOOKING_NAME',
              'customCode' => '<div class="flex-start">
                                  <input name="booking_name" 
                                  type="text" 
                                  size="30" 
                                  maxlength="255" 
                                  value="{$BOOKING_NAME}" 
                                  readonly>

                                  <input name="booking_id" 
                                        type="hidden" 
                                        value="{$fields.booking_id.value}"/>

                                  <input title="{$APP.LBL_SELECT_BUTTON_TITLE}" 
                                        type="{$BOOKING_BUTTON_AVAILABILITY}" 
                                        class="btn btn-primary" 
                                        value="{$APP.LBL_SELECT_BUTTON_LABEL}" 
                                        name="btn2" 
                                        onclick=\'open_popup("EC_Flight_Bookings", 600, 400, "", true, false, {$encoded_booking_popup_data}, "single", true);\'/>
                              </div>',
            ),
          ),
          array(
            array(
              'name' => 'related_doc_name',
              'customCode' => '<div class="flex-start"><input name="related_document_name" type="text" size="30" maxlength="255" value="{$RELATED_DOCUMENT_NAME}" readonly><input name="related_doc_id" type="hidden" value="{$fields.related_doc_id.value}"/><input title="{$APP.LBL_SELECT_BUTTON_TITLE}" type="{$RELATED_DOCUMENT_BUTTON_AVAILABILITY}" class="btn btn-primary" value="{$APP.LBL_SELECT_BUTTON_LABEL}" name="btn2" onclick=\'open_popup("Documents", 600, 400, "", true, false, {$encoded_document_popup_request_data}, "single", true);\'/></div>',
            ),
            array(
              'name' => 'related_doc_rev_number',
              'customCode' => '<select name="related_doc_rev_id" id="related_doc_rev_id" {$RELATED_DOCUMENT_REVISION_DISABLED}>{$RELATED_DOCUMENT_REVISION_OPTIONS}</select>',
            ),
          ),
        ),
      ),
    ),
  );
