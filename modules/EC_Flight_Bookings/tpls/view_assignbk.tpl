{literal}
    <style>
      
        #online_report .group_img {
            vertical-align: bottom;
        }

        #online_report .offline {
            background-image: repeating-linear-gradient(
                20deg, #ccc, #ccc 30px, #dbdbdb 30px, #dbdbdb 60px
            );
        }

        #online_report .stt_online {
            color: var(--bs-green-2);
            font-weight: 600;
        }

        #online_report .stt_busy {
            color: var(--primary-color);
            font-weight: 600;
        }

        #online_report .col_name {
            position: relative;
        }

        #online_report .col_name .ranking {
            position: absolute;
            right: 10px;
            font-weight: 600;
            color: var(--red-vj-color);
        }

        .blue_bold {
            color: var(--primary-color);
            font-weight: 600;
        }

        .name_loading {
            vertical-align: middle;
            padding-left: 10px;
        }

        #online_report td.group_sip span{
            font-size: 13px;
        }

        #online_tbl thead tr th{
            background: #fff2cc;
            color: #000;
        }
    </style>

    <script>
        $(document).ready(function() {
            resizeCusBtn();
            $(window).resize(function() {
                resizeCusBtn();
            });

            $(document).on("click", ".online_btn", function() {
                var prior_full_name     = $(this).attr("full_name") || '';
                var change_type         = $(this).attr("change_type");
                var selected_col_name   = $(this).closest("tr").children(".col_name");

                $(".online_btn").prop("disabled", true);

                if($(this).attr("change_type") == 'up') {
                    moveThisToTop(
                        $(this).closest("tr")
                        ,$(this).closest("tr").attr("class")
                        ,$(this).closest("tr").attr("ln")
                        ,$(this).closest("table")
                    );
                } 
                else if($(this).attr("change_type") == 'down') {
                    moveThisToBottom(
                        $(this).closest("tr")
                        , $(this).closest("tr").attr("class")
                        , $(this).closest("tr").attr("ln")
                        , $(this).closest("table")
                    );
                }
                else if($(this).attr("change_type") == 'off') {
                    let tr_this = $(this).closest("tr");
                    tr_this.find("td:last-child").html("");
                    tr_this.find("td.status").removeClass("stt_online");
                    tr_this.find("td.status").removeClass("stt_busy");
                    tr_this.find("td.status").html("Offline");
                    tr_this.addClass("offline");
                }

                $.ajax({
                    url: "index.php?entryPoint=entryPointFlightBookings",
                    type: "POST",
                    data: {
                        type: $(this).attr("change_type"),
                        onl: $(this).attr("onl_val"),
                        agent: $(this).attr("data-sip"),
                        for: "changeOnlinePosition",
                    },
                    beforeSend: function() {
                        if(change_type == 'priority') {
                            selected_col_name.append("<img class='name_loading' src='themes/SuiteP/images/loading.gif' width='20'>");
                        }
                    },
                    success: function(response) {
                        $(".name_loading").remove();
                        if(response == 'is_over') {
                            alert("Không thể chuyển " + prior_full_name + " sang chế độ ưu tiên vì vượt quá sl tối đa (3 người)");
                            $(".online_btn").prop("disabled", false);
                        } else if(change_type == 'priority') { 

                            $(".online_btn").prop("disabled", false);
                            selected_col_name.removeClass("blue_bold");

                            if(response == 1) {
                                selected_col_name.addClass("blue_bold");
                            }
                        }
                    }
                });
            });
            $.fn.outerHTML = function() {
                return $(this).clone().wrap('<div></div>').parent().html();
            }
        });

        function resizeCusBtn() {
            if($(this).width() < 1050) { 
                $("#online_report .prior_btn").val("PRI"); 
            } else if($(this).width() < 1250) {
                $("#online_report .prior_btn").val("PRIOR"); 
            } else { 
                $("#online_report .prior_btn").val("PRIORITY"); 
            }
        }

        function moveThisToTop(selected_row, row_class, row_num, table) {
            var elemHeight  = selected_row.height();
            var elemTop     = selected_row.position().top;
            var moveUp      = elemTop - table.position().top - elemHeight + 3;
            var moveDown    = elemHeight;
            var listNum     = row_num;
            var listHtml    = selected_row.outerHTML();

            var i = 1;
            $("." + row_class).each(function() {
                if (listNum == i) {
                    return false;
                }
                $(this).css({
                    'transform': 'translateY(' + moveDown + 'px)'
                    , 'transition-duration': '0.5s'
                });
                $(this).children('.col_no').text(i + 1);
                $(this).attr("ln", i + 1);
                i++;
            });
            
            selected_row.css({
                'transform': 'translateY(-' + moveUp + 'px)'
                , 'transition-duration': '0.5s'
            });

            selected_row.children('.col_no').text(1);
            selected_row.attr("ln", 1);
            setTimeout(function() {
                selected_row.remove();
                table.find("tbody").prepend(selected_row);
                $("." + row_class).attr("style", "");
                $(".online_btn").prop("disabled", false);
            }, 1000);
        }

        function moveThisToBottom(selected_row, row_class, row_num, table) {
            var elemHeight  = selected_row.height();
            var elemTop     = selected_row.position().top;
            var moveUp      = elemHeight;
            var listNum     = row_num;
            var listHtml    = selected_row.outerHTML();
            var moveDown    = 0;

            var i = 1;
            $("." + row_class).each(function() {
                if (listNum < i) {
                    $(this).css({
                        'transform': 'translateY(-' + moveUp + 'px)'
                        , 'transition-duration': '0.5s'
                    });
                    moveDown = $(this).position().top;
                    $(this).children('.col_no').text(i - 1);
                    $(this).attr("ln", i - 1);
                }
                i++;
            });
            
            selected_row.attr("ln", i - 1);
            selected_row.children('.col_no').text(i - 1);
            moveDown -= elemTop;

            if(moveDown > 0) {
                selected_row.css({
                    'transform': 'translateY(' + moveDown + 'px)'
                    , 'transition-duration': '0.5s'
                });
            }
            setTimeout(function() {
                selected_row.remove();
                table.find("." + row_class).last().after(selected_row);
                $("." + row_class).attr("style", "");
                $(".online_btn").prop("disabled", false);
            }, 1000);
        }
    </script>
{/literal}

<h1 class="title title-online_tbl">Danh sách Online / Offline</h1>
<div id="online_report" class="box-section">
    <table id="online_tbl" class="table-online_tbl table-details__booking" cellpadding="0" cellspacing="0">
        <thead>
            <th class="hide-mobile" width="3%">STT</th>
            <th>Họ tên</th>
            <th width="7%">SIP</th>
            <th width="7%">Tình trạng</th>
            <th class="hide-mobile" width="12%">Nhóm</th>
            <th class="hide-mobile" width="15%">Check-in</th>
            <th class="hide-mobile" width="7%">Nhận cuộc gọi</th>
            <th class="hide-mobile" width="15%">Last Online</th>
            <th width="18%"></th>
        </thead>
        <tbody>
            {$ONLINE_DATA}
        </tbody>
    </table>
</div>

{if $IS_ALLOWED_USER}
<!-- <div class="box-section">
    <form action="index.php" method="post" name="frmSearch" id="frmSearch" class="mb-3">
        <input type="hidden" name="module" value="EC_Flight_Bookings" />
        <input type="hidden" name="action" value="assignbk" />
        <input type="hidden" name="type" value="view_report_online" />

        <div class="d-flex align-items-center gap-3">
            {$LIST_USER}
            <h4 class="sub-admin__title title-online__report m-0">Lịch sử online {$USER_NAME} </h4>
        </div>
    </form>

    {$VIEW_REPORT_ONLINE}
</div> -->
{/if}