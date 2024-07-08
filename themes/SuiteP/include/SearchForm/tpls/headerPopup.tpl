<div id="searchDialog" class="modal fade modal-search" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{$APP.LBL_FILTER_HEADER_TITLE}</h4>
                <div class="modal-tabs-wrap">
                    <!-- Nav tabs -->
                    <ul class="nav-tabs tabs-search__theme admin_tabs-list" role="tablist">
                        <li class="admin_tabs-item searchTabHandler basic">
                            <a class="nav-link " href="javascript:void(0)" onclick="listViewSearchIcon.toggleSearchDialog('basic'); return false;" aria-controls="searchList" role="tab" data-bs-toggle="tab">{$APP.LBL_QUICK_FILTER}</a>
                        </li>
                        <li class="admin_tabs-item searchTabHandler advanced">
                            <a class="nav-link active" href="javascript:void(0)" onclick="listViewSearchIcon.toggleSearchDialog('advanced'); return false;" aria-controls="searchList" role="tab" data-bs-toggle="tab">{$APP.LBL_ADVANCED_SEARCH}</a>
                        </li>
                    </ul>
                </div>

                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                    </svg>
                </button>
                
            </div>
            <div class="modal-body" id="searchList">