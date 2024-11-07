{literal}
<script>
    $(document).on("click", ".bank_btn", function() {
        const change_type = $(this).attr("change_type");

        if($(this).attr("change_type") == 'up') {
            moveThisToTop(
                $(this).closest("tr")
                ,$(this).closest("tr").attr("class")
                ,$(this).closest("tr").attr("ln")
                ,$(this).closest("table")
            );
        } 
        else if($(this).attr("change_type") == 'down' || $(this).attr("change_type") == 'copy') {
            moveThisToBottom(
                $(this).closest("tr")
                , $(this).closest("tr").attr("class")
                , $(this).closest("tr").attr("ln")
                , $(this).closest("table")
            );

            if($(this).attr("change_type") == 'copy'){
                const bank_name = 'Ngân hàng: '+$(this).attr('stk_bank_name');
                const stk       = 'STK: ' + $(this).attr('stk_name');
                const holder    = 'Chủ sở hữu: '+$(this).attr('stk_bank_holder');
                const content_copy = bank_name + '\n' + stk + '\n' + holder;
                copyContent(content_copy);
            }
        }

        // AJAX
        $.ajax({
            url: "index.php?entryPoint=entryPointBankAccount",
            type: "POST",
            data: {
                type: $(this).attr("change_type"),
                stk: $(this).attr("stk_id"),
                for: "changeBankAccountPosition",
            },
            beforeSend: function() {
               
            },
            success: function(response) {
               console.warn(response);
            }
        });
    });
    $.fn.outerHTML = function() {
        return $(this).clone().wrap('<div></div>').parent().html();
    }

    function moveThisToTop(selected_row, row_class, row_num, table) {
        var elemHeight  = selected_row.height(); //42
        var elemTop     = selected_row.position().top; //79
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
            $(".bank_btn").prop("disabled", false);
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
            $(".bank_btn").prop("disabled", false);
        }, 1000);
    }
</script>
{/literal}
<h1 class="title">Danh sách tài khoản ngân hàng</h1>
<div class="table-responsive box-section text-nowrap">
    <table id="bank_tbl" class="table-banks table-details__booking" cellpadding="0" cellspacing="0">
        <thead>
            <th class="hide-mobile" width="5%">STT</th>
            <th width="15%">STK</th>
            <th width="20%">Ngân hàng</th>
            <th width="10%">Tên viết tắt</th>
            <th width="15%">Chủ tài khoản</th>
            <th class="d-none" width="10%">Lần lấy gần nhất</th>
            <th>Mô tả</th>
            <th width="15%"></th>
        </thead>
        <tbody>
            {$BANKS_LIST}
        </tbody>
    </table>
</div>