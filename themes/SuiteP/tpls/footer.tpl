
<!-- END of container-fluid, pageContainer divs -->
</div>
</div>


{literal}
    <script>
        SUGAR_callsInProgress++;
        SUGAR._ajax_hist_loaded = true;
        if (SUGAR.ajaxUI)
            YAHOO.util.Event.onContentReady('ajaxUI-history-field', SUGAR.ajaxUI.firstLoad);

        $(function(){
            if($('#wizard').length) {

                // footer fix
                var bodyHeight = $('body').height();
                var contentHeight = $('#pagecontent').height() + $('#wizard').height();
                var fieldsetHeight = $('#pagecontent').height() + $('#wizard fieldset').height();
                var height = bodyHeight < contentHeight ? contentHeight : bodyHeight;
                if(fieldsetHeight > height) {
                    height = fieldsetHeight;
                }
                height += 50;
                $('#content').css({
                    'min-height': height + 'px'
                });
            }
        });

    </script>
{/literal}
</div>
    <div class="modal fade modal-generic" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="title-generic">{$APP.LBL_GENERATE_PASSWORD_BUTTON_TITLE}</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" data-bs-dismiss="modal">{$APP.LBL_CANCEL}</button>
                    <button id="btn-generic" class="btn btn-danger" type="button">{$APP.LBL_OK}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    {if $AUTHENTICATED}
     <script>
         window.ECChatbotWidgetConfig = {
             role: 'admin',
             wsUrl: '{$APP_CONFIG.chat_widget.wsUrl|escape:'javascript'}',
             restUrl: '{$APP_CONFIG.chat_widget.restUrl|escape:'javascript'}',
             restKey: '{$APP_CONFIG.chat_widget.restKey|escape:'javascript'}',
             adminId: '{$CURRENT_USER_ID|escape:'javascript'}',
             adminName: '{$CURRENT_USER|escape:'javascript'}',
             autoConnect: true,
             debug: true
         };
     </script>
     <script src="custom/services/widget/admin_widget.js?v=2"></script>
{/if}
</body>
</html>
