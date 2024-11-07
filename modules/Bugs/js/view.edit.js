
$(document).ready(function () {
    // Submit event
    $('#EditView').submit(function () {
		let description = $.trim($('#description').val());

        if (description.length < 200) {
            showModalNotify('error', 'Mô tả ít nhất 200 kí tự. Nếu là báo cáo "lỗi" vui lòng mô tả chi tiết thao tác gặp lỗi.');
            $('#description').focus();
            return false;
        }

        return true;
    });

});