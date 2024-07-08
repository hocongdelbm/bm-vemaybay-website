{literal}
    <script>
        $(document).ready(function () {
            $('#port_list').on('change', function () {
                var regExp = /\(([^)]+)\)/;
                var matches = regExp.exec($('#port_list :selected').text());
                $('#carrier').val(matches[1].toLowerCase());
            });
            $('input:radio[name="sms_func"]').on('change', function () {
                if ($(this).val() == 'recharge_amount') {
                    $('label.card_number').show();
                } else {
                    $('label.card_number').hide();
                }
            });
            $('#frmSmsTool').on('submit', function () {
                var carrier = $.trim($('#carrier').val());
                var sms_func = $('input:radio[name="sms_func"]:checked').val();
                var port_list = $('#port_list :selected').val();
                var card_number = $.trim($('#card_number').val());
                if ((sms_func == 'recharge_amount' || sms_func == 'viettel' || sms_func == 'vinaphone' || sms_func == 'mobifone') && port_list == 'all') {
                    alert('Vui lòng chọn 1 SIM để tiếp tục');
                    return false;
                }
                if (sms_func == 'recharge_amount' && card_number == '') {
                    alert('Vui lòng nhập mã thẻ cào để tiếp tục');
                    return false;
                }
                if((sms_func == 'viettel' || sms_func == 'vinaphone' || sms_func == 'mobifone') && sms_func != carrier){
                    alert('Vui lòng chọn đúng gói đăng ký ứng với từng nhà mạng');
                    return false;
                }
            });
        });
    </script>
{/literal}

<h1 class="title d-flex align-items-center gap-1">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-sim" viewBox="0 0 16 16">
        <path d="M2 1.5A1.5 1.5 0 0 1 3.5 0h7.086a1.5 1.5 0 0 1 1.06.44l1.915 1.914A1.5 1.5 0 0 1 14 3.414V14.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-13zM3.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V3.414a.5.5 0 0 0-.146-.353l-1.915-1.915A.5.5 0 0 0 10.586 1H3.5z"/>
        <path d="M5.5 4a.5.5 0 0 0-.5.5V6h2.5V4h-2zm3 0v2H11V4.5a.5.5 0 0 0-.5-.5h-2zM11 7H5v2h6V7zm0 3H8.5v2h2a.5.5 0 0 0 .5-.5V10zm-3.5 2v-2H5v1.5a.5.5 0 0 0 .5.5h2zM4 4.5A1.5 1.5 0 0 1 5.5 3h5A1.5 1.5 0 0 1 12 4.5v7a1.5 1.5 0 0 1-1.5 1.5h-5A1.5 1.5 0 0 1 4 11.5v-7z"/>
    </svg>
    QUẢN LÝ SIM
</h1>

<div class="frmSearch box-section">
    <form method="post" action="index.php" name="frmSmsTool" id="frmSmsTool">
        <input type="hidden" name="module" id="module" value="EC_Flight_Bookings">
        <input type="hidden" name="action" id="action" value="smstool">
        <input type="hidden" name="carrier" id="carrier" value="">

        <div class="function-wrap">
            <label class="form-check-label d-flex align-items-center gap-2" for="check_amount">
                <input class="form-check-input" {$RECHECK_AMOUNT_CHECKED} type="radio" name="sms_func" id="check_amount" value="check_amount" > 
                <span>Kiểm tra tài khoản</span>
            </label>
            <label class="form-check-label d-flex align-items-center gap-2" for="recharge_amount">
                <input class="form-check-input" {$RECHARGE_AMOUNT_CHECKED} type="radio" name="sms_func" id="recharge_amount" value="recharge_amount" > 
                <span>Nạp tiền tài khoản</span>
            </label>
            <label class="form-check-label d-flex align-items-center gap-2" for="viettel">
                <input class="form-check-input" {$VIETTEL_CHECKED} type="radio" name="sms_func" id="viettel" value="viettel" > 
                <span>Đăng ký gói <strong>VT200 (Viettel)</strong></span>
            </label>
            <label class="form-check-label d-flex align-items-center gap-2" for="vinaphone">
                <input class="form-check-input" {$VINAPHONE_CHECKED} type="radio" name="sms_func" id="vinaphone" value="vinaphone" > 
                <span>Đăng ký gói <strong>S50 (Vinaphone)</strong></span>
            </label>
        </div>

        <div class="d-flex gap-2 align-items-center mt-3">
            <select id="port_list" name="port_list">
                {$PORT_LIST}
            </select>
            <label class="card_number text-label" style="display: none" for="card_number">Mã thẻ cào: <input type="text" name="card_number" class="box-input" id="card_number" value=""></label>
            <input type="submit" class="btn btn-primary button-action" name="btnProcess" id="btnProcess" value="Xử lý" title="Xử lý">
        </div>
    </form>
</div>

{if $RESP_DATA != ''}
<div class="response_data box-section">
    <textarea readonly="readonly" name="resp_data" class="box-textarea" id="resp_data">{$RESP_DATA}</textarea>
</div>
{/if}