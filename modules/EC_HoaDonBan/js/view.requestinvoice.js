$(document).ready(function () {
    addToValidate('search_form', 'from_date', 'date', false, 'Ngày phải nhập theo cú pháp: 31-12-2024');
    addToValidate('search_form', 'to_date', 'date', false, 'Ngày phải nhập theo cú pháp: 31-12-2024');
    $("#search_form").submit(function () {
        if (!check_form('search_form')) {
            return false;
        }

        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();
        if(parseDate(from_date, cal_date_format) > parseDate(to_date, cal_date_format)) {
            showToastWarning('Ngày xem không hợp lệ');
            return false;
        }
        else if(getDaysBetweenDates(from_date, to_date, cal_date_format) > 30) {
            showToastWarning('Khoảng thời gian tìm kiếm quá 30 ngày');
            return false;
        }
    });

    $(document).on('click', '.checkbox_output_invoice_checked', function () {
        let booking_id = $(this).attr('booking_id');
        let is_checked = $(this).is(':checked') ? 1 : 0;

        $.ajax({
                url: "index.php?entryPoint=entryPointFlightBookings",
                type: "POST",
                data: {
                    for: "check_output_invoice",
                    booking_id: booking_id,
                    is_checked: is_checked
                },
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    $('.container-waiting').hide();
                    try {
                        let obj = JSON.parse(response);
                        if (obj.error != 0) $(this).prop('checked', false);
                    }
                    catch(err) {
                        $(this).prop('checked', false);
                        console.error(err);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    showToastWarning('ERROR: Vui lòng liên hệ bộ phận IT!');

                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
    });
});

function parseDate(dateStr, format) {
  const formatParts = format.split(/[-\/]/);
  const dateParts = dateStr.split(/[-\/]/);

  const dateMap = {};

  formatParts.forEach((part, index) => {
    if (part === '%d') dateMap.day = parseInt(dateParts[index], 10);
    if (part === '%m') dateMap.month = parseInt(dateParts[index], 10) - 1; // zero-based
    if (part === '%Y') dateMap.year = parseInt(dateParts[index], 10);
  });

  return new Date(dateMap.year, dateMap.month, dateMap.day);
}

function getDaysBetweenDates(dateStr1, dateStr2, format) {
  const date1 = parseDate(dateStr1, format);
  const date2 = parseDate(dateStr2, format);

  const diffTime = Math.abs(date2 - date1);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}