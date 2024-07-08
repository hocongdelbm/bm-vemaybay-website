{if !$columnsFilterDialogAdded}
    <div id="columnsFilterDialog" class="modal fade modal-columns-filter" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{$APP.LBL_COLUMNS_FILTER_HEADER_TITLE}</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body" id="columnsFilterList"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{$APP.LBL_CLOSE_BUTTON_TITLE}</button>
                    <button type="button" onclick="columnsFilter.onSaveClick();" type="button" class="btn btn-primary">{$APP.LBL_SAVE_CHANGES_BUTTON_TITLE}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    <div id="chooserTemplate" class="hidden">
        <div class="chooserContent d-flex gap-3 flex-column justify-content-center">
            <div class="chooserList-wrap">
                <h3>{$APP.LBL_DISPLAYED}</h3>
                <ul class="chooserList green d-flex gap-2 flex-column justify-content-center"></ul>
            </div>

            <div id="error-displayed-columns" class="error"></div>

            <div class="chooserList-wrap">
                <h3>{$APP.LBL_HIDDEN}</h3>
                <ul class="chooserList hidden-list d-flex gap-2 flex-column justify-content-center"></ul>
            </div>
        </div>
    </div>
{literal}
    <script>

        // TODO add it to sListView
        if (typeof columnsFilter == 'undefined') {

            var columnsFilter = {

                onOpen: function () {
                    this.loadColumnsSettings();
                },

                showContents: function (contents) {
                    $('#columnsFilterList').html(contents);
                },

                showPreload: function () {
                    this.showContents('<p class="preloading"></p>');
                },

                loadColumnsSettings: function () {
                    var _this = this;
                    this.showPreload();

                    if (typeof module_sugar_grp1 != 'undefined' && module_sugar_grp1) {

                        var url = 'index.php?module=' + module_sugar_grp1 + '&action=index&search_form_only=true&to_pdf=true&search_form_view=advanced_search&columnsFilter=true';

                        var cObj = YAHOO.util.Connect.asyncRequest('GET', url, {
                            success: function (e) {
                                _this.showContents(_this.getDragDropChooserHTML(JSON.parse($('<div></div>').html(e.responseText).find('#responseData').html())));
                                _this.initDragDropChooser();
                            }, failure: function () {
                                _this.showContents('ERR_COMMUNICATION_ERROR');
                            }
                        });
                    }
                    else {
                        _this.showContents('ERR_NO_MODULE_SELECTED');
                    }

                },

                getDragDropChooserHTML: function (chooserData) {

                    $('#chooserTemplate .chooserList.green').html('');
                    $.each(chooserData.args.values_array[0], function (key, value) {
                        $('#chooserTemplate .chooserList.green').append('<li data-bs-key="' + key + '">' + value + '</li>');
                    });

                    $('#chooserTemplate .chooserList.red').html('');
                    $.each(chooserData.args.values_array[1], function (key, value) {
                        $('#chooserTemplate .chooserList.red').append('<li data-bs-key="' + key + '">' + value + '</li>');
                    });

                    return $('#chooserTemplate').html();
                },

                initDragDropChooser: function () {
                    var _this = this;
                    $("#columnsFilterList .chooserContent .chooserList.green").sortable({
                        connectWith: "#columnsFilterList .chooserContent .chooserList.red",
                        stop: function () {
                            _this.isValid();
                        }
                    });
                    $("#columnsFilterList .chooserContent .chooserList.green").disableSelection();

                    $("#columnsFilterList .chooserContent .chooserList.red").sortable({
                        connectWith: "#columnsFilterList .chooserContent .chooserList.green",
                        stop: function () {
                            _this.isValid();
                        }
                    });
                    $("#columnsFilterList .chooserContent .chooserList.red").disableSelection();

                },

                onSaveClick: function () {
                    if (this.isValid()) {
                        this.save();
                    }
                },

                // validation (return true if valid, otherwise show error(s) and return false)
                isValid: function () {
                    // clear error message
                    $('#error-displayed-columns').html('');
                    // check validation for empty list
                    var v = $('#columnsFilterList > div > ul.chooserList.green.ui-sortable li').length > 0;
                    if (!v) {
                        // show error
                        $('#error-displayed-columns').html('{/literal}{$APP.ERR_EMPTY_COLUMNS_LIST}{literal}');
                        // scroll to error message
                        $('#columnsFilterDialog').animate({
                            scrollTop: $("#error-displayed-columns").offset().top - 100
                        });
                    }
                    // return validation result
                    return v;
                },

                // send it to server to save user preferences (refresh the page to show changes)
                save: function () {

                    // TODO : show loading message...

                    // make query columns list
                    var cols = [];
                    $('#columnsFilterList > div > ul.chooserList.green.ui-sortable > li').each(function (i, e) {
                        cols.push($(e).attr('data-bs-key'));
                    });

                    $.post($('#search_form').attr('action'), {
                        displayColumns: cols.join('|'),
                        query: 'true',
                        use_stored_query: 'true',
                        update_stored_query: 'true',
                        update_stored_query_key: 'displayColumns',
                        last_search_tab: listViewSearchIcon.getLatestSearchDialogType(),
                        save_columns_order: 'true'
                    }, function () {
                        //close form and refresh page after save
                        $('#columnsFilterDialog > div > div > div.modal-footer > button.btn.button.purple.btn-default').click();
                        if ($('#search_form').length > 0) {
                            document.location.href = $('#search_form').attr('action');
                        } else {
                            if (typeof module_sugar_grp1 != 'undefined' && module_sugar_grp1) {
                                document.location.href = 'index.php?module=' + module_sugar_grp1 + '&action=index';
                            } else {
                                document.location.href = document.location.href;
                            }
                        }
                    });

                }

            };

        }

    </script>
{/literal}

    {assign var="columnsFilterDialogAdded" value="true"}
{/if}