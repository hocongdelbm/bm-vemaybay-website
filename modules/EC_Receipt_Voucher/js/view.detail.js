$(document).ready(function () {
    if (rv_status == '1' && is_current_user_admin) {
        $('form[name="DetailView"] input:button[name="Edit"]').remove();
        $('form[name="DetailView"] input:submit[name="Delete"]').remove();
    }

    function checkBeforeSubmit(booking_id, booking_name, rv_status, isAdmin) {
        if (rv_status == 1 && ['4', '5', '14', '27'].includes(loai_thu) && amount != total_sell) {
            showToastWarning('Số tiền và tổng giá bán phải bằng nhau!');
            return false;
        }

        if (booking_id != '' && booking_name != '' && rv_status == 1) {
            let is_paid = 0;
            let upd_paid = isAdmin ? '' : '&upd_paid=1';

            $.ajax({
                cache: false,
                type: 'POST',
                data: 'booking_id=' + booking_id + '&rv_status=' + rv_status + upd_paid,
                url: 'index.php?entryPoint=entryPointCheckBookingPaid',
                async: false,
                success: function (output) {
                    is_paid = output;
                }
            });

            if (isAdmin && is_paid == 0) {
                showToastWarning('Booking ' + booking_name + ' chưa nhấn đã thanh toán.');
                return false;
            }
        }
        return true;
    }

    // Check booking is paid before receipted
    $('#frmChangeStatus').submit(function () {
        var booking_id      = $('#frmChangeStatus input:hidden[name="booking_id"]').val();
        var booking_name    = $('#frmChangeStatus input:hidden[name="booking_name"]').val();
        var rv_status       = $('#frmChangeStatus input:hidden[name="rv_status"]').val();

        if (!checkBeforeSubmit(booking_id, booking_name, rv_status, false)) {
            return false;
        }
    });

    // Check booking is paid before receipted
    $('#frmAdminChangeStatus').submit(function () {
        let booking_id      = $('#frmAdminChangeStatus input:hidden[name="booking_id"]').val();
        let booking_name    = $('#frmAdminChangeStatus input:hidden[name="booking_name"]').val();
        let rv_status       = $('#rv_status :selected').val();

        if (!checkBeforeSubmit(booking_id, booking_name, rv_status, true)) {
            return false;
        }
    });
});