const ENDPOINT = "index.php?entryPoint=entryPointGeneral";

$(document).ready(function () {
    $('.button-mass-signing').click(function() {
        // const checkboxes = document.querySelectorAll('input.listview-checkbox:checked');
        const checkboxes = Array.from(document.querySelectorAll('input.listview-checkbox:checked')).reverse();
        if (checkboxes.length === 0) {
            alert('Vui lòng chọn ít nhất một dòng!');
            return;
        }

        // Build modal HTML
        const modalId = 'selectedRowsModal';
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove(); // remove old modal if exists

        let isConfirm = true;
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

                if(key == 'lienhe') {
                    const objName = td.querySelector('.name');
                    const objLabelName = td.querySelector('.label-name');
                    const textName = objName ? objName.textContent.trim() : '';
                    const textLabelName = objLabelName ? objLabelName.textContent.trim() : '';
                    data[key] = `${textLabelName} <b>${textName}</b>`.trim();
                }
                else data[key] = value;
            });

            if(data.tinhtrang != 'Ghi sổ' || !data.name) return;
            if(data.name.length < 10 || data.kyhieuhd.length < 6) isConfirm = false;

            tableRows += `<tr>
                <td>${index + 1}</td>
                <td>${data.name || ''}</td>
                <td>${data.ngayhoadon || ''}</td>
                <td>${data.kyhieuhd || ''}</td>
                <td>${data.lienhe || ''}</td>
                <td id="status-${data.name}">${data.tinhtrang || ''}</td>
            </tr>`;

            // dataForSigning[recordId] = data.name;
            dataForSigning[data.name] = recordId;
        });

        // Convert entries to array
        const sortedEntries = Object.entries(dataForSigning).sort(([keyA], [keyB]) => keyA.localeCompare(keyB));
        // Rebuild the object
        dataForSigning = Object.fromEntries(sortedEntries);

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
                                        <th>Chứng từ</th>
                                        <th>Ngày HĐ</th>
                                        <th>Ký hiệu HĐ</th>
                                        <th>Cá nhân/Công ty</th>
                                        <th>Tình trạng</th>
                                    </tr>
                                </thead>
                                <tbody>${tableRows}</tbody>
                            </table>
                            <p style="${!isConfirm ? 'color:red; font-weight:600;' : ''}">Kiểm tra ký hiệu hóa đơn và thông tin trước khi xác nhận</p>
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
            if(!isConfirm) {
                event.preventDefault();
                alert("Vui lòng kiểm tra lại Chứng từ và Ký hiệu hóa đơn");
                return;
            }

            if(confirm("Tiến hành ký số toàn bộ danh sách hóa đơn trên")) {
                const button = event.currentTarget;
                const dataForSigning = decodeDataAttrs(button.dataset.forSigning);
                button.disabled = true;

                const sendRequests = async (dataMap) => {
                    const entries = Object.entries(dataMap); // [[id, code], ...]

                    const requests = entries.map(([code, id], index) => {
                        setTimeout(() => {
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
                        }, index * 200);
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