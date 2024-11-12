
$(document).ready(function () {
    // Submit event
    $('#EditView').submit(function () {
		let description     = $.trim($('#description').val());
		let photo_file     = $.trim($('#photo').val());
		let photo_sub_file     = $.trim($('#photo_sub').val());

        if (description.length < 200) {
            showModalNotify('error', 'Mô tả ít nhất 200 kí tự. Nếu là báo cáo "lỗi" vui lòng mô tả chi tiết thao tác gặp lỗi.');
            $('#description').focus();
            return false;
        }

        if (photo_file.length == 0 && photo_sub_file.length == 0) {
            showModalNotify('error', 'Vui lòng bổ sung hình minh họa.');
            return false;
        }

        return true;
    });
});