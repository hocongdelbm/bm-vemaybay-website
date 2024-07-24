{{capture name=display_size assign=size}}{{$displayParams.size|default:6}}{{/capture}}
{php}$this->_tpl_vars['user_options'] = get_user_array(false);{/php}
{html_options name='{{$vardef.name}}[]' options=$user_options size="{{$size}}" {{if $size > 1}}multiple="1"{{/if}} selected={{sugarvar key='value' string=true}}}
