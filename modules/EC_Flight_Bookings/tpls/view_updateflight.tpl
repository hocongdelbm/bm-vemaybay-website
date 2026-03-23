<div class="container">
    <div class="header">
        <form id="searchForm" class="searchForm row g-3 justify-content-center text-dark rounded">
            <!-- Hãng hàng không -->
            <div class="col">
                <label class="form-label">Hãng<span class="text-danger">*</span></label>
                <select id="airlineCode" class="form-select" required>
                    <option value="VJ" selected>VJ</option>
                    <option value="VN">VN</option>
                    <option value="QH">QH</option>
                    <option value="VU">VU</option>
                    <option value="9G">9G</option>
                </select>
            </div>

            <!-- Sân bay đi -->
            <div class="col">
                <label class="form-label">Nơi đi<span class="text-danger">*</span></label>
                {* <input type="text" id="depCode" class="form-control" placeholder="SGN" required> *}
                <select id="depCode" class="form-select airport-select" required>
                    <option value="">Chọn nơi đi...</option>
                    <option value="SGN">Hồ Chí Minh (SGN)</option>
                    <option value="HAN">Hà Nội (HAN)</option>
                    <option value="HPH">Hải Phòng (HPH)</option>
                    <option value="VDO">Vân Đồn (VDO)</option>
                    <option value="DAD">Đà Nẵng (DAD)</option>
                    <option value="VCA">Cần Thơ (VCA)</option>
                    <option value="PQC">Phú Quốc (PQC)</option>
                    <option value="CXR">Nha Trang (CXR)</option>
                    <option value="DLI">Đà Lạt (DLI)</option>
                    <option value="THD">Thanh Hóa (THD)</option>
                    <option value="VII">Vinh (VII)</option>
                    <option value="HUI">Huế (HUI)</option>
                    <option value="VDH">Đồng Hới (VDH)</option>
                    <option value="VCL">Chu Lai (VCL)</option>
                    <option value="UIH">Quy Nhơn (UIH)</option>
                    <option value="TBB">Tuy Hòa (TBB)</option>
                    <option value="PXU">Pleiku (PXU)</option>
                    <option value="BMV">Buôn Ma Thuột (BMV)</option>
                    <option value="VCS">Côn Đảo (VCS)</option>
                    <option value="VKG">Rạch Giá (VKG)</option>
                    <option value="CAH">Cà Mau (CAH)</option>
                    <option value="DIN">Điện Biên (DIN)</option>
                </select>
            </div>

            <!-- Sân bay đến -->
            <div class="col">
                <label class="form-label">Nơi đến<span class="text-danger">*</span></label>
                {* <input type="text" id="desCode" class="form-control" placeholder="HAN" required> *}
                <select id="desCode" class="form-select airport-select">
                    <option value="">Chọn nơi đi...</option>
                    <option value="SGN">Hồ Chí Minh (SGN)</option>
                    <option value="HAN">Hà Nội (HAN)</option>
                    <option value="HPH">Hải Phòng (HPH)</option>
                    <option value="VDO">Vân Đồn (VDO)</option>
                    <option value="DAD">Đà Nẵng (DAD)</option>
                    <option value="VCA">Cần Thơ (VCA)</option>
                    <option value="PQC">Phú Quốc (PQC)</option>
                    <option value="CXR">Nha Trang (CXR)</option>
                    <option value="DLI">Đà Lạt (DLI)</option>
                    <option value="THD">Thanh Hóa (THD)</option>
                    <option value="VII">Vinh (VII)</option>
                    <option value="HUI">Huế (HUI)</option>
                    <option value="VDH">Đồng Hới (VDH)</option>
                    <option value="VCL">Chu Lai (VCL)</option>
                    <option value="UIH">Quy Nhơn (UIH)</option>
                    <option value="TBB">Tuy Hòa (TBB)</option>
                    <option value="PXU">Pleiku (PXU)</option>
                    <option value="BMV">Buôn Ma Thuột (BMV)</option>
                    <option value="VCS">Côn Đảo (VCS)</option>
                    <option value="VKG">Rạch Giá (VKG)</option>
                    <option value="CAH">Cà Mau (CAH)</option>
                    <option value="DIN">Điện Biên (DIN)</option>
                </select>
            </div>
            {*  *}
            <div class="col">
                <label class="form-label">Ngày đi<span class="text-danger">*</span></label>
                <div class="between_range_section between_range_end--wrap dateTime d-flex position-relative flex-fill">
                    <input autocomplete="off" type="text" name="start_range_date_ticket_issue_advanced" id="departDate"
                        value="" title="" tabindex="" size="11" class="dateRangeInput date_input w-100" maxlength="10">
                    <button id="start_range_date_ticket_issue_advanced_trigger" type="button" onclick="return false"
                        class="icon_dateTime">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" alt="Nhập vào ngày"
                            fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path
                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z">
                            </path>
                            <path
                                d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z">
                            </path>
                        </svg>
                    </button>

                    {literal}
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: "departDate",
                                daFormat: "%d-%m-%Y",
                                button: "start_range_date_ticket_issue_advanced_trigger",
                                singleClick: true,
                                dateStr: "",
                                step: 1,
                                weekNumbers: false
                            });
                        </script>
                    {/literal}
                </div>
            </div>

            <div class="col">
                <label class="form-label">Ngày về</label>
                <div class="between_range_section between_range_end--wrap dateTime d-flex position-relative flex-fill">
                    <input autocomplete="off" type="text" name="end_range_date_entered_advanced" id="returnDate"
                        value="" title="" tabindex="" size="11" class="dateRangeInput date_input w-100" maxlength="10">
                    <button id="end_range_date_entered_advanced_trigger" type="button" onclick="return false"
                        class="icon_dateTime">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" alt="Nhập vào ngày"
                            fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path
                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z">
                            </path>
                            <path
                                d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z">
                            </path>
                        </svg>
                    </button>

                    {literal}
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: "returnDate",
                                daFormat: "%d-%m-%Y",
                                button: "end_range_date_entered_advanced_trigger",
                                singleClick: true,
                                dateStr: "",
                                step: 1,
                                weekNumbers: false
                            });
                        </script>
                    {/literal}
                </div>
            </div>

            <!-- Nút tìm kiếm -->
            <div class="col d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" id="btnFare">Xem giá</button>
            </div>

            <div class="col d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"
                    onclick="return confirm('Thao tác sẽ cập nhật lại giá mới nhất của tất cả chuyến bay trong hành trình, luôn cả giá vừa chỉnh sửa.')">
                    Giá mới nhất
                </button>

            </div>
        </form>
    </div>


    <div class="tabs">
        <a href="#departure" class="tab active" onclick="showTab(event, 'departure')">Chiều đi</a>
        <a href="#return" class="tab" onclick="showTab(event, 'return')">Chiều về</a>
    </div>

    <div id="departure" class="tab-content active">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div class="section-title" id="depTitle">Chuyến bay đi</div>
            {* bình thuong sẽ ẩn nút btnFare_dep_all sẽ ẩn cho đến khi function displayFlightData(data) gọi xuất hiện buttun *}
            <div class="col d-flex align-items-end btnFare-update-all"> 
                <button type="button" style="display: none;" class="btn btn-primary " id="btnFare_dep_all">Cập nhật hàng loạt</button>
            </div>
            <div id="depCountdown" class="countdown text-danger"></div>
        </div>
        <div class="flight-list" id="depFlightList">
            <div class="loading">Nhập thông tin và nhấn tìm kiếm để xem chuyến bay</div>
        </div>
    </div>

    <div id="return" class="tab-content">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div class="section-title" id="retTitle">Chuyến bay về</div>
            {* bình thuong sẽ ẩn nút btnFare_ret_all sẽ ẩn cho đến khi function displayFlightData(data) gọi xuất hiện buttun *}
            <div class="col d-flex align-items-end btnFare-update-all">
                <button type="button" style="display: none;" class="btn btn-primary " id="btnFare_ret_all">Cập nhật hàng loạt</button>
            </div>
            <div id="retCountdown" class="countdown text-danger"></div>
        </div>
        <div class="flight-list" id="retFlightList">
            <div class="loading">Nhập thông tin và nhấn tìm kiếm để xem chuyến bay</div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh Sửa Giá Vé -->
<div class="modal fade" id="editFlightModal" tabindex="-1" aria-labelledby="editFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md-lg">
        <div class="modal-content">
            <div class="modal-header flight-modal">
                <h5 class="modal-title" id="editFlightModalLabel">
                    <i class="bi bi-pencil-square"></i> Cập nhật thông tin chuyến bay
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body bg-light">
                <div class="container-fluid text-center">

                    <!-- Hãng hàng không -->
                    <div class="airline-display mb-4">
                        <span id="modal_airlineCode_display"
                            class="airline-badge px-4 py-2 rounded-pill text-white fw-bold">
                            VietJet Air
                        </span>
                        <input type="hidden" id="modal_airlineCode">
                    </div>

                    <!-- Tuyến bay (tên và giờ đi/đến) -->
                    <div class="route-section mb-4">
                        <div class="row align-items-center flight-info-dep-des">
                            <!-- Nơi đi -->
                            <div class="col-md-5 col-sm-12 text-end dep-info">
                                <div class="airport-name fw-bold" id="modal_depName">Hồ Chí Minh</div>
                                <div class="airport-code text-dark" id="modal_depCode">(SGN)</div>
                                <div class="time-info text-secondary" id="modal_depTime">18:45 - 12/10/2025</div>
                            </div>

                            <!-- Mũi tên ở giữa -->
                            <!-- Mũi tên ở giữa + số hiệu -->
                            <div class="col-md-2 col-sm-12 route-arrow text-primary text-center flightNo-dep-des">
                                <div class="fs-3">✈</div>
                                <div class="flight-number text-primary fw-bold fs-5" id="modal_flightNo">VJ168</div>
                            </div>


                            <!-- Nơi đến -->
                            <div class="col-md-5 col-sm-12 text-start des-info">
                                <div class="airport-name fw-bold" id="modal_desName">Hà Nội</div>
                                <div class="airport-code text-dark" id="modal_desCode">(HAN)</div>
                                <div class="time-info text-secondary" id="modal_arvTime">20:55 - 12/10/2025</div>
                            </div>
                        </div>
                    </div>

                    <!-- Giá vé -->
                    <div class="row justify-content-start">
                        <div class="col-md-6" style="
                                                    text-align: left;">
                            <label class="info-label">Giá cơ bản <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded-3">
                                <input type="text" class="form-control editable-field" id="modal_fare"
                                    placeholder="Nhập giá vé" min="0" step="1000" required>
                                <span class="input-group-text fw-bold bg-primary text-white">VND</span>
                            </div>
                            <small class="text-dark">
                                Giá cơ bản gốc: <span id="modal_originalFare" class="fw-bold text-dark">0</span> VND
                            </small>
                            <small id="fareError" class="text-danger" style="display:none;">
                                Vui lòng nhập giá trị hợp lệ
                            </small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancel-modal" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary btn-update" onclick="updateFlightFare()">
                    Cập nhật 
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh Sửa Giá Vé Hàng Loạt -->
<div class="modal fade" id="editFlightModal_all" tabindex="-1" aria-labelledby="editFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md-lg">
        <div class="modal-content">
            <div class="modal-header flight-modal">
                <h5 class="modal-title" id="editFlightModalLabel">
                    <i class="bi bi-pencil-square"></i> Cập nhật giá vé hàng loạt 
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body bg-light">
                <div class="container-fluid text-center">

                    <!-- Hãng hàng không -->
                    <div class="airline-display mb-4">
                        <span id="modal_airlineCode_display_all"
                            class="airline-badge px-4 py-2 rounded-pill text-white fw-bold">
                            VietJet Air
                        </span>
                        <input type="hidden" id="modal_airlineCode_all">
                    </div>

                    <!-- Tuyến bay (tên và giờ đi/đến) -->
                    <div class="route-section mb-4">
                        <div class="row align-items-center flight-info-dep-des">
                            <!-- Nơi đi -->
                            <div class="col-md-5 col-sm-12 text-end dep-info">
                                <div class="airport-name fw-bold" id="modal_depName_all">Hồ Chí Minh</div>
                                <div class="airport-code text-dark" id="modal_depCode_all">(SGN)</div>
                                
                            </div>

                            <!-- Mũi tên ở giữa -->
                            <!-- Mũi tên ở giữa + số hiệu -->
                            <div class="col-md-2 col-sm-12 route-arrow text-primary text-center flightNo-dep-des">
                                <div class="fs-3">✈</div>
                                <div class="time-info text-secondary" id="modal_depTime_all">12/10/2025</div>
                            </div>

                            <!-- Nơi đến -->
                            <div class="col-md-5 col-sm-12 text-start des-info">
                                <div class="airport-name fw-bold" id="modal_desName_all">Hà Nội</div>
                                <div class="airport-code text-dark" id="modal_desCode_all">(HAN)</div>
                                
                            </div>
                        </div>
                    </div>

                    <!-- Giá vé -->
                    <div class="row justify-content-start">
                        <div class="col-md-6 content-price" style="
                                                    text-align: left;">
                            <label class="info-label">Giá thay đổi <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded-3">
                                <input type="text" class="form-control editable-field" id="modal_fare_all"
                                    placeholder="Nhập giá muốn điều chỉnh" min="0" step="1000" required>
                                <span class="input-group-text fw-bold bg-primary text-white">VND</span>
                            </div>
                            <!-- Thông báo động -->
                            <div id="fareChangeMessage" class="mt-2" style="display:none;">
                                <span id="fareChangeText" class="fw-bold"></span>
                            </div>                       
                            <small id="fareError_all" class="text-danger" style="display:none;">
                                Vui lòng nhập giá trị hợp lệ
                            </small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancel-modal" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary btn-update btn-update-all" onclick="updateFlightFareAll()">
                    Cập nhật giá
                </button>
            </div>
        </div>
    </div>
</div>

