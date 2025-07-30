$(document).ready(function() {
    $('#update_fare').click(function() {
        let depCode = $('input[name="depCode"]').val().toUpperCase();
        let arvCode = $('input[name="arvCode"]').val().toUpperCase();
        let depDate = $('input[name="depDate"]').val();
        let retDate = $('input[name="retDate"]').val();

        if(depCode == arvCode) {
            alert('Hành trình không hợp lệ');
            return;
        }
        if(retDate.length > 0) {
            const d1 = new Date(depDate);
            const d2 = new Date(retDate);

            if(d1 > d2) {
                alert('Ngày đi, ngày về không hợp lệ');
                return;
            }
        }

        $.ajax({
            type: "POST",
            url: "index.php?entryPoint=entryPointUpdateFareSystem",
            data: {
                action: "update-flight-fare",
                depCode: depCode,
                arvCode: arvCode,
                depDate: depDate,
                retDate: retDate
            },
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (response) {
                $('.container-waiting').hide();

                if(response.length > 0) {
                    let obj = JSON.parse(response);
                    if(obj.error == 0) {
                        showModalNotify(1, `Đã cập nhật giá vé mới nhất`);
                        return true;
                    }
                    else {
                        let m = obj.message ? obj.message : 'Cập nhật thất bại';
                        showModalNotify(0, m);
                        return;
                    }
                }
                else {
                    showModalNotify(0, 'Cập nhật thất bại, vui lòng thử lại sau');
                    return;
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.container-waiting').hide();
                showModalNotify(0, 'Cập nhật thất bại, vui lòng thử lại sau');
                console.error(XMLHttpRequest);
                console.error("Status: " + textStatus);
                console.error("Error: " + errorThrown);
            }
        });
    });
});