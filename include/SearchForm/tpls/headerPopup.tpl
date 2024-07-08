<div id="searchDialog" class="modal fade modal-search" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{$APP.LBL_FILTER_HEADER_TITLE}</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"></path>
                      </svg>    
                </button>
            </div>
            
            <div class="modal-body" id="searchList">
                <ul class="admin_tabs-list tabs-search__include" id="tabs-list-search" role="tablist">
                    <li class="admin_tabs-item" role="presentation">
                      <a class="nav-link active" onclick="listViewSearchIcon.toggleSearchDialog('basic'); return false;" id="basic__search-tab" data-bs-toggle="tab" type="button" role="tab" aria-controls="basic-search-pane" aria-selected="true">
                        {$APP.LBL_QUICK_FILTER}
                      </a>
                    </li>
                    <li class="admin_tabs-item" role="presentation">
                      <a class="nav-link" onclick="listViewSearchIcon.toggleSearchDialog('advanced'); return false;" id="advance__search-tab" data-bs-toggle="tab" type="button" role="tab" aria-controls="advance-search-pane" aria-selected="false">
                        {$APP.LBL_ADVANCED_SEARCH}
                      </a>
                    </li>
                </ul>