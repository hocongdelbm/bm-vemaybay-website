if (typeof ENTRYPOINT === 'undefined') {
    const ENTRYPOINT = 'index.php?entryPoint=entryPointGeneral';
}

function showUploadedDocuments(booking_id) {
    showLoadingPopup();

    $.ajax({
        url: ENTRYPOINT,
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({
            class: 'entryBookingClass',
            method: 'getUploadedDocuments',
            params: {
                booking_id: booking_id
            }
        }),
        success: function (response) {
            hideLoadingPopup();

            if (response.success) {
                // Cập nhật badge với số thực tế
                var count = (response.documents || []).length;
                var badge = document.getElementById('doc-count-badge');
                if (badge) {
                    badge.textContent = count;
                    badge.className = count > 0
                        ? 'doc-count-badge'
                        : 'doc-count-badge doc-count-badge--empty';
                }
                renderDocumentPopup(response.documents);
            } else {
                alert('Lỗi: ' + (response.error || 'Không thể tải dữ liệu'));
            }
        },
        error: function (xhr, status, error) {
            hideLoadingPopup();
            console.error('Lỗi kết nối: ' + error);
        }
    })
}

function renderDocumentPopup(documents) {
    // Xóa popup cũ nếu có
    var oldPopup = document.getElementById('uploaded-docs-dialog');
    if (oldPopup) {
        oldPopup.remove();
    }

    // Tạo HTML cho popup
    var html = '<div id="uploaded-docs-dialog" class="custom-dialog-overlay">' +
        '<div class="custom-dialog-container">' +
        '<div class="custom-dialog-header">' +
        '<h3 class="m-0 sub-title">Danh sách tài liệu đã tải lên</h3>' +
        '<button class="custom-dialog-close" onclick="closeDocumentPopup()">&times;</button>' +
        '</div>' +
        '<div class="custom-dialog-body">' +
        '<table class="custom-table">' +
        '<thead>' +
        '<tr>' +
        '<th>Tên tài liệu</th>' +
        '<th>Ảnh</th>' +
        '<th>Danh mục</th>' +
        '<th>Ngày tải</th>' +
        '<th>Người tải</th>' +
        '<th>Thao tác</th>' +
        '</tr>' +
        '</thead>' +
        '<tbody>';

    if (documents && documents.length > 0) {
        documents.forEach(function (doc) {
            var downloadUrl = doc.doc_url + '/download';

            html += '<tr>' +
                '<td><a href="index.php?module=Documents&action=DetailView&record=' + doc.id + '" target="_blank" style="color: #0a58ca; text-decoration: none;">' + doc.document_name + '</a></td>' +
                '<td>' + (doc.preview_image && doc.preview_image !== "Không phải file ảnh" && doc.preview_image !== "" ? '<img src="' + doc.preview_image + '" alt="Preview" style="max-width: 100px; max-height: 100px;">' : 'Không có preview') + '</td>' +
                '<td>' + doc.category + '</td>' +
                '<td>' + doc.date_entered + '</td>' +
                '<td>' + doc.created_by_name + '</td>' +
                '<td>' +
                '<a href="' + downloadUrl + '" class="uiverse-btn" target="_blank" class="tabDetailViewDFLink"><span class="box box-success">Tải</span></a>' +
                '</td>' +
                '</tr>';
        });
    } else {
        html += '<tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">Chưa có tài liệu nào</td></tr>';
    }

    html += '</tbody>' +
        '</table>' +
        '</div>' +
        '<div class="custom-dialog-footer">' +
        '<button class="btn btn-secondary custom-btn custom-btn-default" onclick="closeDocumentPopup()">Đóng</button>' +
        '</div>' +
        '</div>' +
        '</div>';

    // Thêm CSS nếu chưa có
    if (!document.getElementById('custom-dialog-styles')) {
        var style = document.createElement('style');
        style.id = 'custom-dialog-styles';
        style.textContent = `
            .custom-dialog-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s ease;
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            .custom-dialog-container {
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                max-width: 900px;
                width: 90%;
                max-height: 80vh;
                display: flex;
                flex-direction: column;
                animation: slideDown 0.3s ease;
            }
            @keyframes slideDown {
                from { transform: translateY(-50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .custom-dialog-header {
                padding: 20px;
                border-bottom: 1px solid #ddd;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f8f9fa;
                border-radius: 8px 8px 0 0;
            }
            .custom-dialog-close {
                background: none;
                border: none;
                font-size: 30px;
                cursor: pointer;
                color: #999;
                line-height: 1;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: all 0.2s;
            }
            .custom-dialog-close:hover {
                background: #e9ecef;
                color: #333;
            }
            .custom-dialog-body {
                padding: 20px;
                overflow-y: auto;
                flex: 1;
            }
            .custom-table th {
                background: #f8f9fa;
                padding: 12px;
                text-align: left;
                border-bottom: 2px solid #ddd;
                font-weight: 600;
                color: #495057;
            }
            .custom-table td {
                padding: 12px;
                border-bottom: 1px solid #ddd;
            }
            .custom-table tr:hover {
                background: #f8f9fa;
            }
            .custom-dialog-footer {
                padding: 15px 20px;
                border-top: 1px solid #ddd;
                text-align: right;
                background: #f8f9fa;
                border-radius: 0 0 8px 8px;
            }
           .uiverse-btn {
                text-decoration: none;
                cursor: pointer;
                outline: none;
                border: none;
                background: transparent;
                display: inline-block; /* Quan trọng để nút nằm hàng ngang */
                padding: 0; /* Reset padding của thẻ a nếu có */
            }

            .box {
                height: auto;
                display: inline-block; 
                transition: .5s linear;
                position: relative;
                overflow: hidden;
                padding: 5px 10px; /* Căn chỉnh lại padding cho vừa mắt */
                font-size: 12px;
                text-align: center;
                margin: 0 2px;
                background: transparent;
                text-transform: uppercase;
                font-weight: 700;
                color: #0a58ca; 
            }

            .box:before {
                position: absolute;
                content: '';
                left: 0;
                bottom: 0;
                height: 4px;
                width: 100%;
                border-bottom: 2px solid transparent;
                border-left: 2px solid transparent;
                box-sizing: border-box;
                transform: translateX(100%);
            }

            .box:after {
                position: absolute;
                content: '';
                top: 0;
                left: 0;
                width: 100%;
                height: 4px;
                border-top: 2px solid transparent;
                border-right: 2px solid transparent;
                box-sizing: border-box;
                transform: translateX(-100%);
            }


            .box:hover {
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                background-color: rgba(10, 88, 202, 0.05); /* Thêm chút nền nhạt khi hover cho đẹp */
            }

            .box:hover:before {
                border-color: #0a58ca; /* Đổi sang màu xanh */
                height: 100%;
                transform: translateX(0);
                transition: .1s transform linear, .1s height linear .1s;
            }

            .box:hover:after {
                border-color: #0a58ca; /* Đổi sang màu xanh */
                height: 100%;
                transform: translateX(0);
                transition: .1s transform linear, .1s height linear .1s; 
            }
            .box.box-success {
                color: #28a745;
            }
            .box.box-success:hover {
                background-color: rgba(40, 167, 69, 0.05); 
            }

            .box.box-success:hover:before {
                border-color: #28a745;
            }

            .box.box-success:hover:after {
                border-color: #28a745;
            }
        `;
        document.head.appendChild(style);
    }

    // Thêm popup vào body
    document.body.insertAdjacentHTML('beforeend', html);
}

function closeDocumentPopup() {
    var popup = document.getElementById('uploaded-docs-dialog');
    if (popup) {
        popup.style.animation = 'fadeOut 0.3s ease';
        setTimeout(function () {
            popup.remove();
        }, 300);
    }
}

function showLoadingPopup() {
    var html = '<div id="loading-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; ' +
        'background:rgba(0,0,0,0.6); z-index:10000; display:flex; align-items:center; justify-content:center;">' +
        '<div style="background:white; padding:30px 40px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.3); text-align:center;">' +
        '<div class="spinner" style="border:4px solid #f3f3f3; border-top:4px solid #3498db; border-radius:50%; ' +
        'width:50px; height:50px; animation:spin 1s linear infinite; margin:0 auto;"></div>' +
        '<p style="margin-top:15px; margin-bottom:0; color:#333; font-size:16px;">Đang tải...</p>' +
        '</div>' +
        '</div>';

    if (!document.getElementById('spinner-keyframes')) {
        var style = document.createElement('style');
        style.id = 'spinner-keyframes';
        style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }

    document.body.insertAdjacentHTML('beforeend', html);
}

function hideLoadingPopup() {
    var loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}