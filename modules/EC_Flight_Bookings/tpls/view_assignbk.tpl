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
                        $(this).closest("tr"),
                        $(this).closest("table")
                    );
                }
                else if($(this).attr("change_type") == 'down') {
                    moveThisToBottom(
                        $(this).closest("tr"),
                        $(this).closest("table")
                    );
                }
                else if($(this).attr("change_type") == 'busy') {
                    let tr_this = $(this).closest("tr");
                    tr_this.find("td.status").removeClass("stt_online").addClass("stt_busy").html("Busy");
                    tr_this.removeClass("change_pos_valid").removeClass("offline").addClass("busy");
                }
                else if($(this).attr("change_type") == 'delete') {
                    if(!confirm("Xóa user này khỏi bảng hôm nay?")) {
                        $(".online_btn").prop("disabled", false);
                        return;
                    }
                    $(this).closest("tr").remove();
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
                        } else {
                            // up/down đã có setTimeout re-enable trong animation, off/busy thì cần re-enable tại đây
                            if(change_type == 'off' || change_type == 'busy' || change_type == 'delete') {
                                $(".online_btn").prop("disabled", false);
                            }
                        }
                    }
                });
            });
            $.fn.outerHTML = function() {
                return $(this).clone().wrap('<div></div>').parent().html();
            }

            $("#btn_update_online").on("click", function() {
                var $btn = $(this).prop("disabled", true).text("Đang cập nhật...");
                $.post("index.php?entryPoint=entryPointFlightBookings", { for: "updateOnlineReport" }, function() {
                    $.get("index.php?entryPoint=entryPointFlightBookings&for=getOnlineStatus", function(html) {
                        if (html && html.trim().length > 0) {
                            $("#online_report").html(html);
                        }
                        $btn.prop("disabled", false).text("Cập nhật");
                    });
                });
            });
        });

        // Auto-refresh bảng online mỗi 30 giây, bỏ qua khi có action đang diễn ra
        var onlineRefreshTimer = setInterval(function() {
            if ($(".online_btn:disabled").length > 0) return;
            $.get("index.php?entryPoint=entryPointFlightBookings&for=getOnlineStatus", function(html) {
                if (html && html.trim().length > 0) {
                    $("#online_report").html(html);
                }
            });
        }, 30000);

        function resizeCusBtn() {
            if($(this).width() < 1050) { 
                $("#online_report .prior_btn").val("PRI"); 
            } else if($(this).width() < 1250) {
                $("#online_report .prior_btn").val("PRIOR"); 
            } else { 
                $("#online_report .prior_btn").val("PRIORITY"); 
            }
        }

        // UP/DOWN luôn đưa user về nhóm Online (backend luôn set status=1)
        // $anchor được capture TRƯỚC setTimeout để tránh bug "row biến mất"
        // khi selected_row là row duy nhất trong nhóm

        function moveThisToTop(selected_row, table) {
            var $onlineRows = table.find("tr.fw-bold.change_pos_valid");
            var elemHeight  = selected_row.height();
            var elemTop     = selected_row.position().top;

            // Anchor = online row đầu tiên KHÔNG phải selected_row
            var $anchor = null;
            $onlineRows.each(function() {
                if (this !== selected_row[0] && $anchor === null) { $anchor = $(this); }
            });

            var firstTop = $anchor ? $anchor.position().top : elemTop;
            var moveUp   = elemTop - firstTop;

            // Các online rows khác dịch xuống để nhường chỗ
            $onlineRows.each(function() {
                if (this !== selected_row[0]) {
                    $(this).css({ 'transform': 'translateY(' + elemHeight + 'px)', 'transition-duration': '0.5s' });
                }
            });

            if (moveUp > 0) {
                selected_row.css({ 'transform': 'translateY(-' + moveUp + 'px)', 'transition-duration': '0.5s' });
            }

            // Cập nhật trạng thái visual ngay lập tức
            selected_row.removeClass("busy offline").addClass("change_pos_valid");
            selected_row.find("td.status").removeClass("stt_busy").addClass("stt_online").html("Online");

            setTimeout(function() {
                selected_row.remove();
                if ($anchor && $anchor.length) {
                    $anchor.before(selected_row);
                } else {
                    table.find("tbody").prepend(selected_row);
                }
                table.find("tr.fw-bold.change_pos_valid").removeAttr("style");
                selected_row.removeAttr("style");
                $(".online_btn").prop("disabled", false);
            }, 1000);
        }

        function moveThisToBottom(selected_row, table) {
            var $onlineRows = table.find("tr.fw-bold.change_pos_valid");
            var elemHeight  = selected_row.height();
            var elemTop     = selected_row.position().top;

            // Anchor = online row cuối cùng KHÔNG phải selected_row
            var $anchor = null;
            $onlineRows.each(function() {
                if (this !== selected_row[0]) { $anchor = $(this); }
            });

            var lastTop    = $anchor ? $anchor.position().top : elemTop;
            var lastHeight = $anchor ? $anchor.height() : elemHeight;
            var moveDown   = (lastTop + lastHeight) - (elemTop + elemHeight);

            // Các online rows nằm dưới selected_row dịch lên
            $onlineRows.each(function() {
                if (this !== selected_row[0] && $(this).position().top > elemTop) {
                    $(this).css({ 'transform': 'translateY(-' + elemHeight + 'px)', 'transition-duration': '0.5s' });
                }
            });

            if (moveDown !== 0) {
                selected_row.css({ 'transform': 'translateY(' + moveDown + 'px)', 'transition-duration': '0.5s' });
            }

            // Cập nhật trạng thái visual ngay lập tức
            selected_row.removeClass("busy offline").addClass("change_pos_valid");
            selected_row.find("td.status").removeClass("stt_busy").addClass("stt_online").html("Online");

            setTimeout(function() {
                selected_row.remove();
                if ($anchor && $anchor.length) {
                    $anchor.after(selected_row);
                } else {
                    table.find("tbody").prepend(selected_row);
                }
                table.find("tr.fw-bold.change_pos_valid").removeAttr("style");
                selected_row.removeAttr("style");
                $(".online_btn").prop("disabled", false);
            }, 1000);
        }
    </script>
{/literal}

<div class="d-flex align-items-center gap-3 mb-2">
    <h1 class="title title-online_tbl mb-0">Danh sách Online / Offline</h1>
    {if $IS_ALLOWED_USER}
    <button id="btn_update_online" class="btn btn-outline-primary btn-sm">Cập nhật</button>
    {/if}
</div>
<div id="online_report" class="box-section">
    {$ONLINE_DATA}
</div>