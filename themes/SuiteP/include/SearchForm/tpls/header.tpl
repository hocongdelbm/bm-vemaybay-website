<!-- <div class='listViewBody box-section'> -->
<div class='listViewBody'>
    <script type="text/javascript" src="{sugar_getjspath file='include/javascript/popup_parent_helper.js'}"></script>
    {$TABS}
    {{if $displayView == 'saved_views'}}
    {literal}
        <script>SUGAR.savedViews.handleForm();</script>
    {/literal}
    {{/if}}
    {literal}
        <script>
            function addXMLRequestCallback(callback) {
                var oldSend, i;
                if (XMLHttpRequest.callbacks) {
                    // we've already overridden send() so just add the callback
                    XMLHttpRequest.callbacks.push(callback);
                } else {
                    // create a callback queue
                    XMLHttpRequest.callbacks = [callback];
                    // store the native send()
                    oldSend = XMLHttpRequest.prototype.send;
                    // override the native send()
                    XMLHttpRequest.prototype.send = function () {
                        // process the callback queue
                        for (i = XMLHttpRequest.callbacks.length - 1; i >= 0; i--) {
                            XMLHttpRequest.callbacks[i](this);
                        }
                        // call the native send()
                        oldSend.apply(this, arguments);
                    }
                }
            }

            function refreshSearchForm() {
                $('.search_form textarea').each(function (i, e) {
                    $(e).css('max-width', $(e).parent().width());
                });
                if (!$('#search_form .tabFormAdvLink').prev().hasClass('clear')) {
                    $('#search_form .tabFormAdvLink').before('<div class="clear"></div>');
                }
                $('#search_form .dateTimeRangeChoice').css({
                    'white-space': 'initial',
                    'display': 'block'
                });
            }

            $(function () {
                var refreshSearchFormIntervals = [];
                var refreshSearchFormIntervalsCountDown = 100;
                addXMLRequestCallback(function (xhr) {
                    refreshSearchFormIntervalsCountDown = 100;
                    refreshSearchFormIntervals.push(setInterval(function () {
                        refreshSearchForm();
                        refreshSearchFormIntervalsCountDown -= 1 / refreshSearchFormIntervals.length;
                        if (refreshSearchFormIntervalsCountDown <= 0) {
                            $.each(refreshSearchFormIntervals, function (i, e) {
                                clearInterval(e);
                            });
                            refreshSearchFormIntervals = [];
                        }
                    }, 100));
                });
            });

            function submitOnEnter(e) {
                var characterCode = (e && e.which) ? e.which : event.keyCode;
                if (characterCode == 13 && event.target.type !== "textarea") {
                    document.getElementById('search_form').submit();
                    return false;
                } else {
                    return true;
                }
            }
        </script>
    {/literal}

    {if $searchFormInPopup}
        {include file='include/SearchForm/tpls/headerPopup.tpl'}
    {/if}
    
    <div class="overlay-mobile"></div>
    <form name='search_form' id='search_form' class='search_form search_form--in-themes {if !$searchFormInPopup}non-popup{/if}' method='post' action='index.php?module={$module}&action={$action}' onkeydown='submitOnEnter(event);'>
        <input type='hidden' name='searchFormTab' value='{$displayView}'/>
        <input type='hidden' name='module' value='{$module}'/>
        <input type='hidden' name='action' value='{$action}'/>
        <input type='hidden' name='query' value='true'/>

        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
        </svg>

        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" id="btnSearch_cancel" class="bi bi-x-lg button-action--cancel d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
        </svg>

        {foreach name=tabIteration from=$TAB_ARRAY key=tabkey item=tabData}
            <div id='{$module}{$tabData.name}_searchSearchForm' style='{$tabData.displayDiv}' class="action--wrap edit view search {$tabData.name}">{if $tabData.displayDiv}{else}{$return_txt}{/if}</div>
        {/foreach}
        <div id='{$module}saved_viewsSearchForm'
             {{if $displayView != 'saved_views'}}style='display: none;'{{/if}}>{$saved_views_txt}</div>
