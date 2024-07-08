<script>
    {literal}
    $(document).ready(function(){
	    $("ul.clickMenu").each(function(index, node){
	        $(node).sugarActionMenu();
	    });

        if($('.edit-view-pagination').children().length == 0) $('.saveAndContinue').remove();
    });
    {/literal}
</script>

<form action="index.php" method="POST" name="{$form_name}" id="{$form_id}" {$enctype}>
    <div class="edit-view-pagination-mobile-container">
        <div class="edit-view-pagination edit-view-mobile-pagination">
            {{if $SHOW_VCR_CONTROL}}
            {$PAGINATION}
            {{/if}}
        </div>
    </div>

    <div class="row dcQuickEdit">
        <div class="col-md-12">
            <input type="hidden" name="module" value="{$module}">
            {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}
            <input type="hidden" name="record" value="">
            <input type="hidden" name="duplicateSave" value="true">
            <input type="hidden" name="duplicateId" value="{$fields.id.value}">
            {else}
            <input type="hidden" name="record" value="{$fields.id.value}">
            {/if}
            <input type="hidden" name="isDuplicate" value="false">
            <input type="hidden" name="action" value="">
            <input type="hidden" name="return_module" value="{$smarty.request.return_module}">
            <input type="hidden" name="return_action" value="{$smarty.request.return_action}">
            <input type="hidden" name="return_id" value="{$smarty.request.return_id}">
            <input type="hidden" name="module_tab"> 
            <input type="hidden" name="contact_role">
            {if (!empty($smarty.request.return_module) || !empty($smarty.request.relate_to)) && !(isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true")}
            <input type="hidden" name="relate_to" value="{if $smarty.request.return_relationship}{$smarty.request.return_relationship}{elseif $smarty.request.relate_to && empty($smarty.request.from_dcmenu)}{$smarty.request.relate_to}{elseif empty($isDCForm) && empty($smarty.request.from_dcmenu)}{$smarty.request.return_module}{/if}">
            <input type="hidden" name="relate_id" value="{$smarty.request.return_id}">
            {/if}
            <input type="hidden" name="offset" value="{$offset}">
            {assign var='place' value="_HEADER"} <!-- to be used for id for buttons with custom code in def files-->
            {{if isset($form.hidden)}}
            {{foreach from=$form.hidden item=field}}
            {{$field}}   
            {{/foreach}}
            {{/if}}
            {{include file='themes/SuiteP/include/EditView/actions_buttons.tpl'}}
        </div>
        <!-- <div class="w-25 d-none">
            {{$ADMIN_EDIT}}
            {{if $panelCount == 0}}
                {{* Render tag for VCR control if SHOW_VCR_CONTROL is true *}}
                <div class="edit-view-pagination edit-view-pagination-desktop">
                    {{if $SHOW_VCR_CONTROL}}
                    {$PAGINATION}
                    {{/if}}
                </div>
            {{/if}}
        </div> -->
    </div>
