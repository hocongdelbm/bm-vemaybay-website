<div class="panel panel-default panel-email-compose">
    <div class="panel-body">
         {{if !empty($form) && !empty($form.buttons)}}
            <div class="custom-buttons">
                 {{foreach from=$form.buttons key=val item=button}}
                    {{sugar_button module="$module" id="$button" form_id="$form_id" view="$view"}}
                 {{/foreach}}
            </div>
         {{/if}}
    </div>
</div>