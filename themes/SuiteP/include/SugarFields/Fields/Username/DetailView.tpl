<span class="sugar_field" id="{{sugarvar key='name'}}">
{$fields[{{sugarvar key='salutation' stringFormat=true}}].value} {$fields[{{sugarvar key='first_name' stringFormat=true}}].value} {$fields[{{sugarvar key='last_name' stringFormat=true}}].value}
</span>
{{if !empty($displayParams.enableConnectors)}}
{{sugarvar_connector view='DetailView'}} 
{{/if}}