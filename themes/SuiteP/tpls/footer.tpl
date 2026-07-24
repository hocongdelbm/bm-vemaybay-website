
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
{if $APP_CONFIG.chat_widget.enable}
{literal}
    <script>
         window.ECChatbotWidgetConfig = {
             role: 'admin',
             wsUrl: '{/literal}{$APP_CONFIG.chat_widget.wsUrl|escape:'javascript'}{literal}',
             restUrl: '{/literal}{$APP_CONFIG.chat_widget.restUrl|escape:'javascript'}{literal}',
             credentials: '{/literal}{$CHAT_CREDENTIALS_B64|escape:'javascript'}{literal}',
             crmUrl: '{/literal}{$APP_CONFIG.chat_widget.crmUrl|escape:'javascript'}{literal}',
             adminId: '{/literal}{$CURRENT_USER_ID|escape:'javascript'}{literal}',
             adminName: '{/literal}{$CURRENT_USER|escape:'javascript'}{literal}',
             autoConnect: true,
             debug: true
         };
     </script>
     <script src="{/literal}{$APP_CONFIG.chat_widget.restUrl|escape}{literal}/client/admin_widget_v2.js?v=1.1.3"></script>
{/literal}
{/if}
{literal}
     <script>
         // Helper dùng chung: ghi nhận hoạt động cho các thao tác KHÔNG sinh record tracker
         // (AJAX trong-trang, chat widget, cuộc gọi softphone...).
         // Endpoint chỉ UPDATE cột last_activity -> KHÔNG phình bảng.
         // THROTTLE 3 phút: dù click/gõ bao nhiêu lần cũng chỉ gọi tối đa 1 lần / 3 phút
         // (vẫn dư an toàn dưới ngưỡng OFF 5 phút).
         var CRM_ACTIVITY_THROTTLE = 180000; // 3 phút
         window.recordCrmActivity = function () {
             var now = Date.now();
             if (now - (window.__lastCrmActivityPing || 0) < CRM_ACTIVITY_THROTTLE) return;
             window.__lastCrmActivityPing = now;
             try {
                 var url = 'index.php?entryPoint=entryPointRecordActivity';
                 if (navigator.sendBeacon) {
                     navigator.sendBeacon(url, new Blob([], { type: 'application/x-www-form-urlencoded' }));
                 } else {
                     fetch(url, { method: 'POST', keepalive: true });
                 }
             } catch (e) {}
         };

         // Heartbeat tương tác toàn cục: mọi click / gõ phím trong BM -> ghi nhận hoạt động.
         // Bao trùm mọi thao tác AJAX trong-trang (booking detail, chat widget, điền form...) vốn KHÔNG sinh tracker. 
         // Auto-refresh (timer) không phải sự kiện user nên không tính
         // -> vẫn OFF đúng người thật sự rời máy.
         (function () {
             document.addEventListener('click', window.recordCrmActivity, true);
             document.addEventListener('keydown', window.recordCrmActivity, true);
         })();
     </script>
{/literal}
{/if}
</body>
</html>
