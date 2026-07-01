(function () {
    function $id(id) {
        return document.getElementById(id);
    }

    function init() {
        var fileInput = $id('ec_airlines_logo_file');
        var chooseBtn = $id('ec_airlines_logo_choose');
        var removeBtn = $id('ec_airlines_logo_remove');
        if (!fileInput || !chooseBtn || !removeBtn) {
            return;
        }

        chooseBtn.addEventListener('click', function () {
            fileInput.click();
        });

        removeBtn.addEventListener('click', function () {
            $id('logo').value = '';
            $id('ec_airlines_logo_preview').classList.add('d-none');
            $id('ec_airlines_logo_preview').src = '';
            removeBtn.classList.add('d-none');
            fileInput.value = '';
            $id('ec_airlines_logo_status').textContent = '';
        });

        fileInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) {
                return;
            }

            var statusEl = $id('ec_airlines_logo_status');
            statusEl.textContent = 'Đang tải lên...';

            var formData = new FormData();
            formData.append('logo', file);

            fetch('index.php?entryPoint=entryPointUploadAirlineLogo', {
                method: 'POST',
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        $id('logo').value = data.url;
                        var img = $id('ec_airlines_logo_preview');
                        img.src = data.url;
                        img.classList.remove('d-none');
                        removeBtn.classList.remove('d-none');
                        statusEl.textContent = '';
                    } else {
                        statusEl.textContent = 'Lỗi: ' + (data.message || 'Tải lên thất bại');
                    }
                })
                .catch(function () {
                    statusEl.textContent = 'Lỗi kết nối máy chủ';
                });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
