{include file="themes/SuiteP/tpls/_head.tpl"}
<body onMouseOut="closeMenus();">

<!-- loading waiting -->
<div class="container-waiting">
    <div id="waiting-loading">
        <div class="spinner"></div>
    </div>
</div>

<!-- back-to-top -->
<a onclick="SUGAR.util.top();" href="javascript:void(0)" class="back-to-top d-flex align-items-center justify-content-center">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5z"/>
      </svg>
</a>

<!-- TOAST WARNING -->
<div class="toast-warning">
    <div class="toast-content d-flex align-items-center">
         <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="#ec2029" viewBox="0 0 256 256">
              <rect width="256" height="256" fill="none"></rect>
              <circle cx="128" cy="128" r="96" fill="none" stroke="#ec2029" stroke-miterlimit="10" stroke-width="16">
              </circle>
              <line x1="128" y1="80" x2="128" y2="136" fill="none" stroke="#ec2029" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
              <circle cx="128" cy="172" r="12"></circle>
         </svg>
         <div id="toast-content">Chưa có nội dung!</div>
         <div class="toast-close">
              <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="#EC2029" viewBox="0 0 256 256">
                   <rect width="256" height="256" fill="none"></rect>
                   <line x1="200" y1="56" x2="56" y2="200" stroke="#EC2029" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
                   <line x1="200" y1="200" x2="56" y2="56" stroke="#EC2029" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line>
              </svg>
         </div>
    </div>
    <div class="progress">
         <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
</div>

<!-- MODAL -->
<div id="modal-container">
    <div id="modal-content"></div>
    <div class="modal-overlay"></div>
</div>

{if $AUTHENTICATED}
    <div id="ajaxHeader">
        {include file="themes/SuiteP/tpls/_headerModuleList.tpl"}
    </div>
{/if}
{literal}
<input id='ajaxUI-history-field' type='hidden'>
<script type='text/javascript'>
    if (SUGAR.ajaxUI && !SUGAR.ajaxUI.hist_loaded) {
        YAHOO.util.History.register('ajaxUILoc', "", SUGAR.ajaxUI.go);
        {/literal}{if isset($smarty.request.module) && $smarty.request.module != "ModuleBuilder"}{* Module builder will init YUI history on its own *}
        YAHOO.util.History.initialize("ajaxUI-history-field", "ajaxUI-history-iframe");
        {/if}{literal}
    }
</script>
{/literal}
<!-- Start of page content -->

{if $AUTHENTICATED}
<div id="bootstrap-container" class="main bootstrap-container">
    <div id="content" class="content">
        <div id="pagecontent" class=".pagecontent" data-module="{$MODULE_NAME}">
{/if}

