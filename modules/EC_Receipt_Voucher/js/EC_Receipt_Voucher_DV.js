$(document).ready(function () {

    // if receipted
    const chief_accountant_arr = ['Administrator', 'QuanLy'];
    if (rv_status == '1' && chief_accountant_arr.indexOf(current_user_title) == -1) {
        $('form[name="DetailView"] input:button[name="Edit"]').remove();
        $('form[name="DetailView"] input:submit[name="Delete"]').remove();
    }

    // Check booking is paid before receipted
    $('#frmChangeStatus').submit(function () {
        var booking_id      = $('#frmChangeStatus input:hidden[name="booking_id"]').val();
        var booking_name    = $('#frmChangeStatus input:hidden[name="booking_name"]').val();
        var rv_status       = $('#frmChangeStatus input:hidden[name="rv_status"]').val();
        var is_paid         = 0;
        var currentTime     = new Date(new Date().toString().split('GMT')[0]+' UTC').toISOString().split('.')[0].replace('T',' ');

        if (booking_id != '' && booking_name != '' && rv_status == 1) {
            $.ajax({
                cache: false,
                type: 'POST',
                data: 'booking_id=' + booking_id + '&rv_status=' + rv_status + '&upd_paid=1',
                url: 'index.php?entryPoint=entryPointCheckBookingPaid',
                async: false,
                success: function (output) {
                    is_paid = output;
                }
            });
        }

    });

    // Check booking is paid before receipted
    $('#frmAdminChangeStatus').submit(function () {
        let booking_id      = $('#frmAdminChangeStatus input:hidden[name="booking_id"]').val();
        let booking_name    = $('#frmAdminChangeStatus input:hidden[name="booking_name"]').val();
        let rv_status       = $('#rv_status :selected').val();
        let is_paid         = 0;

        if (booking_id != '' && booking_name != '' && rv_status == 1) {
            $.ajax({
                cache: false,
                type: 'POST',
                data: 'booking_id=' + booking_id + '&rv_status=' + rv_status,
                url: 'index.php?entryPoint=entryPointCheckBookingPaid',
                async: false,
                success: function (output) {
                    is_paid = output;
                }
            });
            if (is_paid == 0) {
                $('.toast-warning').addClass('active');
                $('.toast-warning #toast-content').text('Booking ' + booking_name + ' chưa nhấn đã thanh toán.');
                $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
                setTimeout(function () {
                $(".toast-warning").removeClass('active');
                }, 4000);
                
                return false;
            }
        }
    });
});