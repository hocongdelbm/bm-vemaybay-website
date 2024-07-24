<input type="text" name="{{sugarvar key='name'}}"  class={{if empty($displayParams.class) }}"sqsEnabled"{{else}} "{{$displayParams.class}}" {{/if}} {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}  id="{{sugarvar key='name'}}" size="{{$displayParams.size}}" value="{{sugarvar key='value'}}" title='{{$vardef.help}}' autocomplete="off" {{$displayParams.readOnly}} {{$displayParams.field}}>
<input type="hidden" {{if $displayParams.useIdSearch}}name="{{sugarvar memberName='vardef.id_name' key='name'}}"{{/if}} id="{{sugarvar memberName='vardef.id_name' key='name'}}" value="{{sugarvar memberName='vardef.id_name' key='value'}}">
{{if empty($displayParams.hideButtons) }}
<span class="id-ff multiple d-flex gap-2 align-items-center search-view__in-include">
{{if empty($displayParams.clearOnly) }}
<button type="button" name="btn_{{sugarvar key='name'}}" {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}  title="{$APP.LBL_SELECT_BUTTON_TITLE}" class="button{{if empty($displayParams.selectOnly) }} firstChild{{/if}} btn btn-primary" value="{$APP.LBL_SELECT_BUTTON_LABEL}" onclick='open_popup("{{sugarvar key='module'}}", 600, 400, "{{$displayParams.initial_filter}}", true, false, {{$displayParams.popupData}}, "single", true);'>
     <!-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-left" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M2 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1H3.707l10.147 10.146a.5.5 0 0 1-.708.708L3 3.707V8.5a.5.5 0 0 1-1 0v-6z"/>
        </svg> -->
        Chọn
</button>{{/if}}
{{if empty($displayParams.selectOnly) }}
<button type="button" name="btn_clr_{{sugarvar key='name'}}" {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}  title="{$APP.LBL_CLEAR_BUTTON_TITLE}" class="button{{if empty($displayParams.clearOnly) }} lastChild{{/if}} btn btn-danger" onclick="this.form.{{sugarvar key='name'}}.value = ''; this.form.{{sugarvar memberName='vardef.id_name' key='name'}}.value = '';" value="{$APP.LBL_CLEAR_BUTTON_LABEL}">
     <!-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
          <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
     </svg> -->
     Xóa
</button>
{{/if}}
</span>
{{/if}}
{{if !empty($displayParams.allowNewValue) }}
<input type="hidden" name="{{sugarvar key='name'}}_allow_new_value" id="{{sugarvar key='name'}}_allow_new_value" value="true">
{{/if}}
