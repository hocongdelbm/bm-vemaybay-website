</form>

{{if $externalJSFile}}
	require_once("'".$externalJSFile."'");
{{/if}}

{$set_focus_block}

{{if isset($scriptBlocks)}}
	<!-- Begin Meta-Data Javascript -->
	{{$scriptBlocks}}
	<!-- End Meta-Data Javascript -->
{{/if}}

<div class="h3Row box-section footer-module__calls" id="scheduler"></div>

<script type='text/javascript' src='{sugar_getjspath file='include/javascript/popup_helper.js'}'></script>
<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui2.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
  
<script type="text/javascript">
{literal}
SUGAR.calls = {};
var callsLoader = new YAHOO.util.YUILoader({
    require : ["sugar_grp_jsolait"],
    // Bug #48940 Skin always must be blank
    skin: {
        base: 'blank',
        defaultSkin: ''
    },
    onSuccess: function(){
		SUGAR.calls.fill_invitees = function() {
			if (typeof(GLOBAL_REGISTRY) != 'undefined')  {
				SugarWidgetScheduler.fill_invitees(document.EditView);
			}
		}
		var root_div = document.getElementById('scheduler');
		var sugarContainer_instance = new SugarContainer(document.getElementById('scheduler'));
		sugarContainer_instance.start(SugarWidgetScheduler);
		if ( document.getElementById('save_and_continue') ) {
			var oldclick = document.getElementById('save_and_continue').attributes['onclick'].nodeValue;
			document.getElementById('save_and_continue').onclick = function(){
				SUGAR.calls.fill_invitees();
				eval(oldclick);
			}
		}
	}
});
callsLoader.addModule({
    name :"sugar_grp_jsolait",
    type : "js",
    fullpath: "cache/include/javascript/sugar_grp_jsolait.js",
    varName: "global_rpcClient",
    requires: []
});
callsLoader.insert();
YAHOO.util.Event.onContentReady("{/literal}{{$form_name}}{literal}",function() {
    var durationHours = document.getElementById('duration_hours');
    if (durationHours) {
        document.getElementById('duration_minutes').tabIndex = durationHours.tabIndex;
    }

    var reminderChecked = document.getElementsByName('reminder_checked');
    for(i=0;i<reminderChecked.length;i++) {
        if (reminderChecked[i].type == 'checkbox' && document.getElementById('reminder_list')) {
            YAHOO.util.Dom.getFirstChild('reminder_list').tabIndex = reminderChecked[i].tabIndex;
        }
    }
});
{/literal}
</script>
</form>
<form>{{sugar_include type="smarty" file='include/EditView/footer.tpl'}}{* template has a </form>*}

