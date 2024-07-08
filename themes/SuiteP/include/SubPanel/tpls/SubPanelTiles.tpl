<script type="text/javascript" src="{sugar_getjspath file='include/SubPanel/SubPanelTiles.js'}"></script>

<ul class="noBullet" id="subpanel_list">
{foreach from=$subpanel_tabs key=i item=subpanel_tab}
    <li class="noBullet useFooTable" id="whole_subpanel_{$subpanel_tab}">
        {$subpanel_tabs_properties.$i.collapse_subpanels}
        <div class="panel panel-default sub-panel">
            <div class="panel-heading panel-heading-collapse">

            {if $subpanel_tabs_properties.$i.expanded_subpanels == true}
                <a id="subpanel_title_{$subpanel_tab}" class="row g-0 in" role="button" data-bs-toggle="collapse" href="#subpanel_{$subpanel_tab}" aria-expanded="false" onclick="toggleSubpanelCookie('{$subpanel_tab}');">
            {else}
                <a id="subpanel_title_{$subpanel_tab}" class="row g-0 collapsed{if isset($subpanel_tabs_properties.$i.collapsed_override)} collapsed-override{/if}" role="button" data-bs-toggle="collapse" href="#subpanel_{$subpanel_tab}" aria-expanded="false" onclick="showSubPanel('{$subpanel_tab}'); toggleSubpanelCookie('{$subpanel_tab}');">
            {/if}
                    <div class="col-xs-12 col-sm-12 col-md-12 d-flex align-items-center justify-content-between">
                        <span class="panel-heading-text w-100">{$subpanel_tabs_properties.$i.title}</span>
                        <span class="panel-heading-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"></path></svg>
                        </span>
                    </div>
                </a>
            </div>

            {if $subpanel_tabs_properties.$i.expanded_subpanels == true}
                <div class="panel-body panel-collapse collapse show" id="subpanel_{$subpanel_tab}">
            {else}
                <div class="panel-body panel-collapse collapse" id="subpanel_{$subpanel_tab}">
            {/if}
                <div class="tab-content">
                    <div id="list_subpanel_{$subpanel_tab}">
                        {$subpanel_tabs_properties.$i.subpanel_body}
                    </div>
                </div>
            </div>
        </div>
    </li>
{/foreach}
</ul>

{if empty($sugar_config.lock_subpanels) || $sugar_config.lock_subpanels == false}
    {*drag and drop code*}
    <script>
        {literal}
        var SubpanelInit = function() {
            SubpanelInitTabNames({/literal}{$tab_names}{literal});
          {/literal}$('.sub-panel .table-details__booking').footable();{literal}
          // collapse subpanels when device is mobile / tablet
          if($(window).width() <= SUGAR.measurements.breakpoints.large) {
            $('[id^=subpanel] .panel-collapse').removeClass('in');
            $('.panel-heading-collapse a').removeClass('in');
            $('.panel-heading-collapse a').addClass('collapsed');
          }
        }
        var SubpanelInitTabNames = function(tabNames) {
            subpanel_dd = new Array();
            j = 0;
            for(i in tabNames) {
                subpanel_dd[j] = new ygDDList('whole_subpanel_' + tabNames[i]);
                subpanel_dd[j].setHandleElId('subpanel_title_' + tabNames[i]);
                subpanel_dd[j].onMouseDown = SUGAR.subpanelUtils.onDrag;
                subpanel_dd[j].afterEndDrag = SUGAR.subpanelUtils.onDrop;
                j++;
            }
            YAHOO.util.DDM.mode = 1;
        }
        currentModule = '{/literal}{$module}{literal}';
        SUGAR.util.doWhen(
                "typeof(SUGAR.subpanelUtils) == 'object' && typeof(SUGAR.subpanelUtils.onDrag) == 'function'" +
                " && document.getElementById('subpanel_list')",
                SubpanelInit
        );

        {/literal}
    </script>
{/if}

<script type="text/javascript">
    var ModuleSubPanels = {$module_sub_panels};
    {literal}
    setTimeout(function() {
        if(typeof SUGAR.subpanelUtils.currentSubpanelGroup !== "undefined") {
            SUGAR.subpanelUtils.loadSubpanelGroup(SUGAR.subpanelUtils.currentSubpanelGroup);
        }
    }, 500);
    {/literal}
</script>