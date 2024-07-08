{sugar_include type="smarty" file="modules/Activities/tpls/PopupHeader.tpl"}

<div class="content">
    <ul class="nav nav-tabs">
        <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab"
                                                  data-toggle="tab">{$mod.LBL_OVERVIEW}</a>
        </li>
        <li role="presentation"><a href="#tasks" aria-controls="tasks" role="tab"
                                   data-toggle="tab">{$mod.LBL_TASKS}</a></li>
        <li role="presentation"><a href="#meetings" aria-controls="meetings" role="tab"
                                   data-toggle="tab">{$mod.LBL_MEETINGS}</a></li>
        <li role="presentation"><a href="#calls" aria-controls="calls" role="tab" data-toggle="tab">{$mod.LBL_CALLS}</a>
        </li>
        <li role="presentation"><a href="#emails" aria-controls="emails" role="tab"
                                   data-toggle="tab">{$mod.LBL_EMAILS}</a>
        </li>
        <li role="presentation"><a href="#notes" aria-controls="notes" role="tab" data-toggle="tab">{$mod.LBL_NOTES}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="home">
            <table class="list view table-responsive subpanel-table">
                <thead>
                <tr class="footable-header">
                    <th>
                        <img class="blank-space" src="include/images/blank.gif">
                    </th>
                    <th>{$mod.LBL_LIST_SUBJECT}</th>
                    <th>{$mod.LBL_LIST_STATUS}</th>
                    <th>{$mod.LBL_LIST_CONTACT}</th>
                    <th>{$mod.LBL_LIST_DATE}</th>
                </tr>
                </thead>

                <tbody>
                {foreach from=$summaryList key=k item=activity}

                    <!-- BEGIN: row -->
                    <td>
                        <span class="suitepicon suitepicon-module-{$activity.module|lower|replace:'_':'-'}"></span>
                    </td>
                    <td>{$activity.name} {$activity.attachment}</td>
                    <td>{$activity.type} {$activity.status}</td>
                    <td>{$activity.contact_name}</td>
                    <td>{$activity.date_type} {$activity.date_modified}</td>
                    <!--  BEGIN: description -->
                    <tr>
                        <td colspan="1"></td>
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td>{$activity.description}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!--  END: description -->


                {/foreach}

                </tbody>
                <!-- END: row -->
            </table>
        </div>

        <div role="tabpanel" class="tab-pane" id="tasks">
            <table class="list view table-responsive subpanel-table">
                <thead>
                <tr class="footable-header">
                    <th>
                        <img class="blank-space" src="include/images/blank.gif">
                    </th>
                    <th>{$mod.LBL_LIST_SUBJECT}</th>
                    <th>{$mod.LBL_LIST_STATUS}</th>
                    <th>{$mod.LBL_LIST_CONTACT}</th>
                    <th>{$mod.LBL_LIST_DATE}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$taskslist key=k item=activity}

                    <!-- BEGIN: row -->
                    <tr>
                        <td>
                            <span class="suitepicon suitepicon-module-{$activity.module|lower|replace:'_':'-'}"></span>
                        </td>
                        <td>{$activity.name} {$activity.attachment}</td>
                        <td>{$activity.type} {$activity.status}</td>
                        <td>{$activity.contact_name}</td>
                        <td>{$activity.date_type} {$activity.date_modified}</td>
                    </tr>
                    <!--  BEGIN: description -->
                    <tr>
                        <td colspan="1"></td>
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td>{$activity.description}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!--  END: description -->


                {/foreach}

                </tbody>
                <!-- END: row -->
            </table>

        </div>

        <div role="tabpanel" class="tab-pane" id="meetings">
            <table class="list view table-responsive subpanel-table">
                <thead>
                <tr class="footable-header">
                    <th>
                        <img class="blank-space" src="include/images/blank.gif">
                    </th>
                    <th>{$mod.LBL_LIST_SUBJECT}</th>
                    <th>{$mod.LBL_LIST_STATUS}</th>
                    <th>{$mod.LBL_LIST_CONTACT}</th>
                    <th>{$mod.LBL_LIST_DATE}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$meetingList key=k item=activity}

                    <!-- BEGIN: row -->
                    <tr>
                        <td>
                            <span class="suitepicon suitepicon-module-{$activity.module|lower|replace:'_':'-'}"></span>
                        </td>
                        <td>{$activity.name} {$activity.attachment}</td>
                        <td>{$activity.type} {$activity.status}</td>
                        <td>{$activity.contact_name}</td>
                        <td>{$activity.date_type} {$activity.date_modified}</td>
                    </tr>
                    <!--  BEGIN: description -->
                    <tr>
                        <td colspan="1"></td>
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td>{$activity.description}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!--  END: description -->


                {/foreach}

                </tbody>
                <!-- END: row -->
            </table>

        </div>

        <div role="tabpanel" class="tab-pane" id="calls">
            <table class="list view table-responsive subpanel-table">
                <thead>
                <tr class="footable-header">
                    <th>
                        <img class="blank-space" src="include/images/blank.gif">
                    </th>
                    <th>{$mod.LBL_LIST_SUBJECT}</th>
                    <th>{$mod.LBL_LIST_STATUS}</th>
                    <th>{$mod.LBL_LIST_CONTACT}</th>
                    <th>{$mod.LBL_LIST_DATE}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$callsList key=k item=activity}

                    <!-- BEGIN: row -->
                    <tr>
                        <td>
                            <span class="suitepicon suitepicon-module-{$activity.module|lower|replace:'_':'-'}"></span>
                        </td>
                        <td>{$activity.name} {$activity.attachment}</td>
                        <td>{$activity.type} {$activity.status}</td>
                        <td>{$activity.contact_name}</td>
                        <td>{$activity.date_type} {$activity.date_modified}</td>
                    </tr>
                    <!--  BEGIN: description -->
                    <tr>
                        <td colspan="1"></td>
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td>{$activity.description}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!--  END: description -->


                {/foreach}

                </tbody>
                <!-- END: row -->
            </table>

        </div>

        <div role="tabpanel" class="tab-pane" id="emails">
            <table class="list view table-responsive subpanel-table">
                <thead>
                <tr class="footable-header">
                    <th>
                        <img class="blank-space" src="include/images/blank.gif">
                    </th>
                    <th>{$mod.LBL_LIST_SUBJECT}</th>
                    <th>{$mod.LBL_LIST_STATUS}</th>
                    <th>{$mod.LBL_LIST_CONTACT}</th>
                    <th>{$mod.LBL_LIST_DATE}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$emailsList key=k item=activity}

                    <!-- BEGIN: row -->
                    <tr>
                        <td>
                            <span class="suitepicon suitepicon-module-{$activity.module|lower|replace:'_':'-'}"></span>
                        </td>
                        <td>{$activity.name} {$activity.attachment}</td>
                        <td>{$activity.type} {$activity.status}</td>
                        <td>{$activity.contact_name}</td>
                        <td>{$activity.date_type} {$activity.date_modified}</td>
                    </tr>
                    <!--  BEGIN: description -->
                    <tr>
                        <td colspan="1"></td>
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td>{$activity.description}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!--  END: description -->


                {/foreach}

                </tbody>
                <!-- END: row -->
            </table>

        </div>

        <div role="tabpanel" class="tab-pane" id="notes">
            <table class="list view table-responsive subpanel-table">
                <thead>
                <tr class="footable-header">
                    <th>
                        <img class="blank-space" src="include/images/blank.gif">
                    </th>
                    <th>{$mod.LBL_LIST_SUBJECT}</th>
                    <th>{$mod.LBL_LIST_STATUS}</th>
                    <th>{$mod.LBL_LIST_CONTACT}</th>
                    <th>{$mod.LBL_LIST_DATE}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$notesList key=k item=activity}

                    <!-- BEGIN: row -->
                    <tr>
                        <td>
                            <span class="suitepicon suitepicon-module-{$activity.module|lower|replace:'_':'-'}"></span>
                        </td>
                        <td>{$activity.name} {$activity.attachment}</td>
                        <td>{$activity.type} {$activity.status}</td>
                        <td>{$activity.contact_name}</td>
                        <td>{$activity.date_type} {$activity.date_modified}</td>
                    </tr>
                    <!--  BEGIN: description -->
                    <tr>
                        <td colspan="1"></td>
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td>{$activity.description}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!--  END: description -->


                {/foreach}

                </tbody>
                <!-- END: row -->
            </table>

        </div>
    </div>
</div>

{sugar_include type="smarty" file="modules/Activities/tpls/PopupFooter.tpl"}