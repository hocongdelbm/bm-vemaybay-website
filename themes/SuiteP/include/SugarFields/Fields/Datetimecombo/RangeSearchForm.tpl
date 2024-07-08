{{if empty($displayParams.idName)}}
{assign var="id" value={{sugarvar key='name' string=true}} }
{{else}}
{assign var="id" value={{$displayParams.idName}} }
{{/if}}

{if isset($smarty.request.{{$id_range_choice}})}
{*assign var="starting_choice" value=$smarty.request.{{$id_range_choice}}*}
{assign var="starting_choice" value="between"}
{else}
{*assign var="starting_choice" value="="*}
{assign var="starting_choice" value="between"}
{/if}

<div class="clear hidden dateTimeRangeChoiceClear"></div>
<div class="dateTimeRangeChoice d-none" style="white-space:nowrap !important;">
<select id="{$id}_range_choice" name="{$id}_range_choice" onchange="{$id}_range_change(this.value);">
{html_options options={{sugarvar key='options' string=true}} selected=$starting_choice}
</select>
</div>

<div id="{$id}_range_div" class="{if preg_match('/^\[/', $smarty.request.{{$id_range}})  || $starting_choice == 'between'} d-none {else} input-group gap-2 flex-nowrap {/if};" style="{if preg_match('/^\[/', $smarty.request.{{$id_range}})  || $starting_choice == 'between'}display:none{else}display:block{/if};">

    <div class="dateTime d-flex gap-2 position-relative w-100">
        <input class="date_input" autocomplete="off" type="text" name="range_{$id}" id="range_{$id}" value='{if empty($smarty.request.{{$id_range}}) && !empty($smarty.request.{{$original_id}})}{$smarty.request.{{$original_id}}}{else}{$smarty.request.{{$id_range}}}{/if}' title='{{$vardef.help}}' {{$displayParams.field}} {{if !empty($tabindex)}} tabindex='{{$tabindex}}' {{/if}} size="11" class="dateRangeInput">
        {{if !$displayParams.hiddeCalendar}}
            <button id="{$id}_trigger" type="button" onclick="return false;" class="icon_dateTime">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                  </svg>
            </button>
        {{/if}}
    </div>
    

    {{if $displayParams.showFormats}}
    (<span class="dateFormat">{$USER_DATEFORMAT}</span>)
    {{/if}}

    {{if !$displayParams.hiddeCalendar}}
        <script type="text/javascript">
            Calendar.setup ({ldelim}
            inputField : "range_{$id}",
            daFormat : "{$CALENDAR_FORMAT}",
            button : "{$id}_trigger",
            singleClick : true,
            dateStr : "{$date_value}",
            startWeekday: {$CALENDAR_FDOW|default:'0'},
            step : 1,
            weekNumbers:false
            {rdelim});
        </script>
    {{/if}}    
</div>

<div id="{$id}_between_range_div" class="align-items-center gap-1 w-100" style="{if $starting_choice=='between'}display:flex;{else}display:none;{/if}">
    <div class="between_range_section between_range_start--wrap dateTime d-flex position-relative flex-fill">
        {assign var=date_value value={{sugarvar key='value' string=true}} }
        <input autocomplete="off" type="text" name="start_range_{$id}" id="start_range_{$id}" value='{$smarty.request.{{$id_range_start}} }' title='{{$vardef.help}}' {{$displayParams.field}} tabindex='{{$tabindex}}' size="11" class="dateRangeInput date_input w-100">
    
        {{if !$displayParams.hiddeCalendar}}
            <button id="start_range_{$id}_trigger" type="button" onclick="return false" class="icon_dateTime">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" alt="{$APP.LBL_ENTER_DATE}" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                </svg>
            </button>
        {{/if}}
    
        {{if $displayParams.showFormats}}
            (<span class="dateFormat">{$USER_DATEFORMAT}</span>)
        {{/if}}
    
        {{if !$displayParams.hiddeCalendar}}
            <script type="text/javascript">
                Calendar.setup ({ldelim}
                inputField : "start_range_{$id}",
                daFormat : "{$CALENDAR_FORMAT}",
                button : "start_range_{$id}_trigger",
                singleClick : true,
                dateStr : "{$date_value}",
                step : 1,
                weekNumbers:false
                {rdelim}
                );
            </script>
        {{/if}} 
    </div>

    {$APP.LBL_AND}

    <div class="between_range_section between_range_end--wrap dateTime d-flex position-relative flex-fill">
        {assign var=date_value value={{sugarvar key='value' string=true}} }
        <input autocomplete="off" type="text" name="end_range_{$id}" id="end_range_{$id}" value='{$smarty.request.{{$id_range_end}}}' title='{{$vardef.help}}' {{$displayParams.field}} tabindex='{{$tabindex}}' size="11" class="dateRangeInput date_input w-100" maxlength="10">
        {{if !$displayParams.hiddeCalendar}}
            <button id="end_range_{$id}_trigger" type="button" onclick="return false" class="icon_dateTime">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" alt="{$APP.LBL_ENTER_DATE}" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                </svg>
            </button>
        {{/if}}

        {{if $displayParams.showFormats}}
            (<span class="dateFormat">{$USER_DATEFORMAT}</span>)
        {{/if}}

        {{if !$displayParams.hiddeCalendar}}
            <script type="text/javascript">
                Calendar.setup ({ldelim}
                inputField : "end_range_{$id}",
                daFormat : "{$CALENDAR_FORMAT}",
                button : "end_range_{$id}_trigger",
                singleClick : true,
                dateStr : "{$date_value}",
                step : 1,
                weekNumbers:false
                {rdelim}
            );
            </script>
        {{/if}} 
    </div>
</div>


<script type='text/javascript'>

// function {$id}_range_change(val) 
// {ldelim}
//   if(val == 'between') {ldelim}
//      document.getElementById("range_{$id}").value = '';  
//      document.getElementById("{$id}_range_div").style.display = 'none';
//      document.getElementById("{$id}_between_range_div").style.display = ''; 
//   {rdelim} else if (val == '=' || val == 'not_equal' || val == 'greater_than' || val == 'less_than') {ldelim}
//      if((/^\[.*\]$/).test(document.getElementById("range_{$id}").value))
//      {ldelim}
//      	document.getElementById("range_{$id}").value = '';
//      {rdelim}
//      document.getElementById("start_range_{$id}").value = '';
//      document.getElementById("end_range_{$id}").value = '';
//      document.getElementById("{$id}_range_div").style.display = '';
//      document.getElementById("{$id}_between_range_div").style.display = 'none';
//   {rdelim} else {ldelim}
//      document.getElementById("range_{$id}").value = '[' + val + ']';    
//      document.getElementById("start_range_{$id}").value = '';
//      document.getElementById("end_range_{$id}").value = ''; 
//      document.getElementById("{$id}_range_div").style.display = 'none';
//      document.getElementById("{$id}_between_range_div").style.display = 'none';         
//   {rdelim}
// {rdelim}

var {$id}_range_reset = function()
{ldelim}
{$id}_range_change('=');
{rdelim}

YAHOO.util.Event.onDOMReady(function() {ldelim}
if(document.getElementById('search_form_clear'))
{ldelim}
YAHOO.util.Event.addListener('search_form_clear', 'click', {$id}_range_reset);
{rdelim}

{rdelim});

YAHOO.util.Event.onDOMReady(function() {ldelim}
 	if(document.getElementById('search_form_clear_advanced'))
 	 {ldelim}
 	     YAHOO.util.Event.addListener('search_form_clear_advanced', 'click', {$id}_range_reset);
 	 {rdelim}

{rdelim});

YAHOO.util.Event.onDOMReady(function() {ldelim}
    //register on basic search form button if it exists
    if(document.getElementById('search_form_submit'))
     {ldelim}
         YAHOO.util.Event.addListener('search_form_submit', 'click',{$id}_range_validate);
     {rdelim}
    //register on advanced search submit button if it exists
   if(document.getElementById('search_form_submit_advanced'))
    {ldelim}
        YAHOO.util.Event.addListener('search_form_submit_advanced', 'click',{$id}_range_validate);
    {rdelim}

{rdelim});

// this function is specific to range date searches and will check that both start and end date ranges have been
// filled prior to submitting search form.  It is called from the listener added above.
// function {$id}_range_validate(e){ldelim}
//     if (
//             (document.getElementById("start_range_{$id}").value.length >0 && document.getElementById("end_range_{$id}").value.length == 0)
//           ||(document.getElementById("end_range_{$id}").value.length >0 && document.getElementById("start_range_{$id}").value.length == 0)
//        )
//     {ldelim}
//         e.preventDefault();
//         alert('{$APP.LBL_CHOOSE_START_AND_END_DATES}');
//         if (document.getElementById("start_range_{$id}").value.length == 0) {ldelim}
//             document.getElementById("start_range_{$id}").focus();
//         {rdelim}
//         else {ldelim}
//             document.getElementById("end_range_{$id}").focus();
//         {rdelim}
//     {rdelim}

// {rdelim}
function {$id}_range_validate(e){ldelim}
    if (document.getElementById("start_range_{$id}").value.length > 0 && document.getElementById("end_range_{$id}").value.length == 0)
    {ldelim}
            var today = new Date();
            var day         = String(today.getDate()).padStart(2, '0');
            var month       = String(today.getMonth() + 1).padStart(2, '0');
            var year        = today.getFullYear();
            var value_date_end = day + '-' + month + '-' + year;

            document.getElementById("end_range_{$id}").value = value_date_end;
    {rdelim}
{rdelim}

</script>
