/*========== GOM PNR ==========*/
$(document).ready(function() {
    var id_modal_add_luggage = '#modal-add-luggage';
    const url = "index.php?entryPoint=entryPointAPIVietjet"; 

    $(document).on('click', '.btn-add-luggage', function(e) {
        let pnr             = $("#pnr").text();
        let supplier_id     = $("#supplier_id").val();
        let re_key          = $(this).attr('re_key');
        let pass_key        = $(this).attr('pass_key');
        let td_name         = $(this).parent().parent().find('.td_name').html();
        let td_type         = $(this).parent().parent().find('.td_type').html();
        let direction       = -1;
        let direction_format = "";

        if($(this).hasClass("btn-add-luggage-dep")) {
            direction = 0;
            direction_format = "<i style='color:#0d6efd'>Lượt đi</i>";
        }
        else if($(this).hasClass("btn-add-luggage-ret")) {
            direction = 1;
            direction_format = "<i style='color:#dc3545'>Lượt về</i>";
        }

        if (re_key.length == 0 || pass_key.length == 0 || direction_format.length == 0 || pnr.length == 0) {
            alert("Không thể thực hiện thao tác");
            return;
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                action : "add_ancillary",
                type : "get",
                pnr : pnr,
                supplier_id : supplier_id,
                re_key : re_key,
                pass_key : pass_key,
                direction : direction
            },
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function(res) {
                $('.container-waiting').hide();
                let data = JSON.parse(res);

                if (data['error'] === true) {
                    showModalNotify('error', data['message'], data['code']);
                    return;
                }

                // Map value
                $(id_modal_add_luggage + ' form .name__passenger .direction').html(direction_format);
                $(id_modal_add_luggage + ' form .name__passenger .name_info').html(td_name + ' ('+ td_type +')');
                $('select#add-luggage').html('<option value="" purchase_key="">Chọn</option>');
                for (const opt of data['data']) {
                    $('select#add-luggage').append($('<option>', {
                        value: opt['total'],
                        text: opt['description'] + "  -  " + opt['total'].toLocaleString('it-IT', {
                            style: 'currency',
                            currency: 'VND'
                        }).replaceAll('.', ','),
                        purchase_key    : opt['purchase_key'],
                        journey_key     : opt['journey_key'],
                        passenger_key   : opt['passenger_key'],
                        booking_key     : opt['booking_key'],
                        re_key          : re_key,
                    }));
                }

                // Display modal
                showModal(id_modal_add_luggage);
            }
        });
    });

    $('#btn-add-luggage').click(function() {
        let supplier_id = $("#supplier_id").val();
        let option      = $('select#add-luggage').find(":selected");
        let booking_id  = $("#booking_id").val();
        let pnr         = '';
        let status_pnr  = $("#status_pnr").attr('value');

        if(status_pnr == '2'){
            let is_confirm = confirm("PNR đã thanh toán. Thao tác sẽ trừ tiền trực tiếp vào tài khoản đại lý.");
            if (!is_confirm) {
                return false;
            } 
        }

        if (option.attr('purchase_key').length == 0 || url.length == 0) {
            alert("Vui lòng chọn hành lý");
            return;
        }
        else {
            pnr = $("#pnr").text();
        }

        hideModal(id_modal_add_luggage);
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                action          : "add_ancillary",
                type            : "add",
                supplier_id     : supplier_id,
                value           : option.val(),
                purchase_key    : option.attr('purchase_key'),
                journey_key     : option.attr('journey_key'),
                passenger_key   : option.attr('passenger_key'),
                booking_key     : option.attr('booking_key'),
                re_key          : option.attr('re_key'),
                booking_id      : booking_id,
                pnr             : pnr
            },
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function(res) {   
                data = JSON.parse(res);

                $('.container-waiting').hide();
                if (data['error'] === true) {
                    showModalNotify('error',data['message'], data['code']);
                    return;
                }
                else if (data['error'] == 'warning') {
                    showModalNotify('warning', data['message']);
                    return;
                }
                
                refresh(data['message']);
            }
        });
    });
});