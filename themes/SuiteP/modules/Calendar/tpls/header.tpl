{if $controls}

<!-- <div class="clear"></div> -->

<div style='float:left; width: 70%;'>
{foreach name=tabs from=$tabs key=k item=tab}
	<input type="button" class="button" {if $view == $k} selected {/if} id="{$tabs_params[$k].id}" title="{$tabs_params[$k].title}" value="{$tabs_params[$k].title}" onclick="{$tabs_params[$k].link}">
{/foreach}
</div>

<div style="float:left; text-align: right; width: 30%; font-size: 12px;">
	{if $view == "sharedWeek" || $view == "sharedMonth"}
		<input id="userListButtonId" type="button" class="btn btn-info" value="{$MOD.LBL_EDIT_USERLIST}" data-toggle="modal" data-target=".modal-calendar-user-list"">
	{/if}
	{if $view != 'year' && !$print}
	<button id="goto_date_trigger" class="btn btn-danger">
		<span class="dateTime module-calendar">
		<span class="suitepicon suitepicon-module-calendar" alt="{$APP.LBL_ENTER_DATE}" ></span>
					<input type="hidden" id="goto_date" name="goto_date" value="{$current_date}">
					<script type="text/javascript">
					Calendar.setup ({literal}{{/literal}
                      inputField : "goto_date",
                      ifFormat : "%m/%d/%Y",
                      daFormat : "%m/%d/%Y",
                      button : "goto_date_trigger",
                      singleClick : true,
                      dateStr : "{$current_date}",
                      step : 1,
                      onUpdate: goto_date_call,
                      startWeekday: {$start_weekday},
                      weekNumbers:false
                        {literal}}{/literal});
                    {literal}
                    YAHOO.util.Event.onDOMReady(function(){
                      YAHOO.util.Event.addListener("goto_date","change",goto_date_call);
                    });
                    function goto_date_call(){
                      CAL.goto_date_call();
                    }
                    {/literal}
					</script>
	</span>
	</button>
	{/if}
	<input type="button" id="" class="btn btn-info" data-toggle="modal" data-target=".modal-calendar-settings" value="{$MOD.LBL_SETTINGS}">
</div>

<div style='clear: both;'></div>

{/if}


<div class="row monthHeader">
    <div class="col-xs-1">{$previous}</div>
    <div class="col-xs-10 text-center"><h3>{$date_info}</h3></div>
    <div class="col-xs-1 text-right">{$next}</div>
    <br>
</div>
