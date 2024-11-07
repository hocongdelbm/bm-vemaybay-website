{literal}
    <script>
        $(document).ready(function () {
            $('#frmRecoveryOrder').on('submit', function () {
                var bookingNo = $.trim($('#txtBookingNo').val()).replace(/[^a-zA-Z0-9]/g, '');
                if(bookingNo == ''){
                    alert('Số booking không hợp lệ');
                    return false;
                }
            });
        });
    </script>
{/literal}
<h1 class="title d-flex align-items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
    </svg>
      PHỤC HỒI BOOKING BỊ XÓA
</h1>
<div class="recoveryOrderWrapper box-section">
    <form method="post" action="index.php" name="frmRecoveryOrder" id="frmRecoveryOrder">
        <input type="hidden" name="module" id="module" value="EC_Flight_Bookings">
        <input type="hidden" name="action" id="action" value="recoveryorder">

        <div class="d-flex align-items-center gap-2">
            <input class="box-input" type="text" name="txtBookingNo" id="txtBookingNo" value="{$BOOKING_NO}" placeholder="Nhập số booking">
            <input type="submit" class="btn btn-primary" name="btnRecovery" id="btnRecovery" value="Phục hồi" title="Phục hồi">
            {if $ERROR != ''}
                <span class="error">{$ERROR}</span>
            {/if}
            {if $SUCCESS != ''}
                <span class="success">{$SUCCESS}</span>
            {/if}
        </div>
    </form>
</div>