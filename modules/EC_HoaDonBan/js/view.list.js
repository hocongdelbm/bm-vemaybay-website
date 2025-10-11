const ENDPOINT = "index.php?entryPoint=entryPointGeneral";

$(document).ready(function () {
    $('.button-mass-signing').click(function() {
        const checkboxes = document.querySelectorAll('input.listview-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Vui lòng chọn ít nhất một dòng!');
            return;
        }

        // Build modal HTML
        const modalId = 'selectedRowsModal';
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove(); // remove old modal if exists

        let dataForSigning = {};
        let tableRows = '';
        checkboxes.forEach((checkbox, index) => {
            const recordId = checkbox.value;
            const row = checkbox.closest('tr');
            const fields = row.querySelectorAll('td[field]');
            const data = {};

            fields.forEach(td => {
                const key = td.getAttribute('field');
                const value = td.textContent.trim();
                data[key] = value;
            });

            if(data.tinhtrang != 'Ghi sổ') return;

            tableRows += `<tr>
                <td>${index + 1}</td>
                <td>${data.name || ''}</td>
                <td>${data.ngayhoadon || ''}</td>
                <td>${data.loaikh || ''}</td>
                <td>${data.tencongty || ''}</td>
                <td>${data.tongthanhtoan || ''}</td>
                <td id="status-${data.name}">${data.tinhtrang || ''}</td>
            </tr>`;

            dataForSigning[recordId] = data.name;
        });

        const modalHTML = `<div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="${modalId}Label">Ký hóa đơn hàng loạt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <h6>Danh sách hóa đơn đã ghi sổ</h6>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Số chứng từ</th>
                                    <th>Ngày hóa đơn</th>
                                    <th>Loại KH</th>
                                    <th>Tên CTY / KH</th>
                                    <th>Tổng tiền</th>
                                    <th>Tình trạng</th>
                                </tr>
                            </thead>
                            <tbody>${tableRows}</tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="buttonConfirmMassSigning" data-for-signing="${encodeDataAttrs(dataForSigning)}">Xác nhận</button>
                </div>
                </div>
            </div>
        </div>`;

        // Append modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Show modal
        const modalEl = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Handle Confirm click
        modalEl.querySelector('#buttonConfirmMassSigning').addEventListener('click', (event) => {
            if(confirm("Thao tác sẽ tiến hành ký số toàn bộ danh sách hóa đơn trên")) {
                const button = event.currentTarget;
                const dataForSigning = decodeDataAttrs(button.dataset.forSigning);
                button.disabled = true;

                const sendRequests = async (dataMap) => {
                    const entries = Object.entries(dataMap); // [[id, code], ...]

                    const requests = entries.map(([id, code]) => {
                        $(`#status-${code}`).html(`<span class="txt-signing-pending">Đang xử lý <div class="loader-signing"></div></span>`);
                        return fetch(ENDPOINT, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                class: "entryOutputInvoiceClass",
                                method: "sign",
                                params: {
                                    recordId: id,
                                    invRef: code
                                }
                            })
                        })
                        .then(response => {
                            if (!response.ok) $(`#status-${code}`).html(`<span class="txt-signing-failed">Thao tác lỗi ${response.status}</span>`);
                            return response.json();
                        })
                        .then(data => {
                            if('status' in data) {
                                if(data.status == 1) $(`#status-${code}`).html(`<span class="txt-signing-success">${data.message}</span>`);
                                else $(`#status-${code}`).html(`<span class="txt-signing-failed">${data.message}</span>`);
                            }
                            else $(`#status-${code}`).html(`<span class="txt-signing-failed">Thao tác lỗi</span>`);
                        })
                        .catch(error => {
                            $(`#status-${code}`).html(`<span class="txt-signing-failed">${error.message}</span>`);
                            console.error(`Error for ${code}:`, error);
                        });
                    });

                    // Wait for all requests to finish (even if some fail)
                    const results = await Promise.allSettled(requests);
                    console.log("All requests finished:", results);
                };
                sendRequests(dataForSigning);
            }
        });
    }); 
});



function encodeDataAttrs(value) {
    if(!value) return value;
    if(typeof value === "object") return btoa(encodeURIComponent(JSON.stringify(value)));
    if(typeof value === "string") return btoa(encodeURIComponent(value));
}

function decodeDataAttrs(value) {
    if(!value || value.length == 0) return value;
    if(typeof value === "string") return JSON.parse(decodeURIComponent(atob(value)));
}