<h1 class="title title-apivj">XUẤT VÉ VIETJET</h1>
<div class="box-frm__wrap">
    <form method="post" action="index.php" name="frmIssueTicket" id="frmIssueTicket">
        <input type="hidden" name="module" id="module" value="EC_Flight_Bookings">
        <input type="hidden" name="action" id="action" value="issueticket">
        <input type="hidden" name="supplier_id" id="supplier_id" value="">

        <input type="text" class="form-control" name="pnr" placeholder="Nhập PNR">
        <button type="button" class="btn" id="btn-search-pnr">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
        </button>
    </form>
</div>

<!-- SEARCH BY PNR -->
<input type="hidden" name="booking_id" id="booking_id" value="">

<div class="box-section wrap-info-pnr mt-3" style="display:none">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs nav-tabs__api">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#booking_info">THÔNG TIN CHUNG</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#journeys">HÀNH TRÌNH</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#passengers">HÀNH KHÁCH</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#charges_pnr">THUẾ PHÍ</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content box-tabs m-0" id="tabpane-apivj">
        <div class="tab-pane active" id="booking_info">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Nhà cung cấp</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-5 col-7 value" id="supplier_name"></div>
                        <div class="col-md-4 col-12 value text-end fw-bold text-danger mt-3 mt-md-0">Số dư: <span class="fs-5" id="balance_agency">0 VND</span></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">PNR</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="pnr"></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Mã số</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="number"></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Khóa đặt chỗ</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="reservation_key" data-rekey=""></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Email liên hệ</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="email_booking"></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Tình trạng</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="status_pnr" value=""></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Giá mua</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="charges" data=""></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Đã thanh toán</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="payments" data=""></div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-2 col-4 label">Tiền hoàn lại</div>
                        <div class="col-md-1 col-1 dot">:</div>
                        <div class="col-md-9 col-7 value" id="refunds" data=""></div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="tab-pane fade" id="journeys"></div>

        <div class="tab-pane fade" id="passengers">
            <h4 class="subtitle mt-2">Thông tin hành khách</h4>
            <table class="table table-hover table-bordered" id="table-passengers">
                <thead>
                    <tr>
                        <th>Loại</th>
                        <th>Giới tính</th>
                        <th>Họ tên</th>
                        <th>Ngày sinh</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>CCCD / Passport</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-passengers"></tbody>
            </table>

            <h4 class="subtitle mt-5">Thông tin hành lý</h4>
            <table class="table table-hover table-bordered" id="table-options">
                <thead>
                    <tr>
                        <th>Chiều</th>
                        <th>Loại</th>
                        <th>Giới tính</th>
                        <th>Họ tên</th>
                        <th>Hành lý</th>
                        <th>Giá mua</th>
                    </tr>
                </thead>
                <tbody id="tbody-options"></tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="charges_pnr">
            <div id="accordion">
                <div class="card-charges" id="card-charges-dep">
                    <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapse-charges-dep">Hành trình: <span id="charges-journey-dep"></span></a>
                    <div id="collapse-charges-dep" class="collapse" data-bs-parent="#accordion">
                        <div class="card-body"></div>
                    </div>
                </div>

                <div class="card-charges mt-3" id="card-charges-ret" style="display: none;">
                    <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapse-charges-ret">Hành trình: <span id="charges-journey-ret"></span></a>
                    <div id="collapse-charges-ret" class="collapse" data-bs-parent="#accordion">
                        <div class="card-body"></div>
                    </div>
                </div>
            </div> 
        </div>
    </div>

    <div class="wrap-button-payment-pnr text-center">
        <button type="button" class="btn btn-warning mt-4" id="btn-payment-pnr"><i class="fa fa-id-card-o me-2" aria-hidden="true"></i>Xuất vé</button>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modal-notification">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="content"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-lg btn-block close-modal" data-dismiss="modal" flag="">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-confirm-payment">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-id-card-o me-2" aria-hidden="true"></i>Xác nhận thanh toán</h4>
            </div>
            <div class="modal-body">
                <p class="content">Thao tác sẽ trừ tiền vào tài khoản đại lý</p>
                <p class="content">Bạn có muốn thanh toán cho booking <span id="pnr-payment"></span> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary close-modal" id="confirm-payment" data-dismiss="modal">Xác nhận</button>
                <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-luggage">
    <div class="modal-add-baggage">
        <div class="modal__container">
            <div class="modal__featured">
                <div class="modal__circle"></div>
                <img src="modules/EC_Flight_Bookings/assets/images/luggage-icon.png" class="modal__product" />
            </div>
            <div class="modal__content">
                <h2 class="modal__title">THÊM HÀNH LÝ KÝ GỬI</h2>
                <form class="modal-body">
                    <ul class="form-list">
                        <li class="form-list__row">
                            <label>Hành khách</label>
                            <div class="name__passenger">
                                <b class="me-2 direction"></b>
                                <br>
                                <span class="name_info"></span>
                            </div>
                        </li>
                        <li class="form-list__row">
                            <label for="">Hành Lý</label>
                            <div class="select-box">
                                <label class="select" for="slct">
                                    <select class="form-select" id="add-luggage">
                                        <option value="" purchase_key="">Chọn</option>
                                    </select>
                                    <svg>
                                        <use xlink:href="#select-arrow-down"></use>
                                    </svg>
                                </label>
                                <!-- SVG Sprites-->
                                <svg class="sprites">
                                    <symbol id="select-arrow-down" viewbox="0 0 10 6">
                                        <polyline points="1 1 5 5 9 1"></polyline>
                                    </symbol>
                                </svg>
                            </div>
                        </li>

                        <li class="form-list__row--button">
                            <button type="button" class="button-modal mr-10" id="btn-add-luggage" data-dismiss="modal">Thêm</button>
                            <button type="button" class="button-modal close-modal btn-close-modal" data-dismiss="modal">Đóng</button>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-update-passenger">
    <div class="modal-update-passenger">
        <div class="modal__container">
            <div class="modal__featured">
                <div class="modal__circle"></div>
                <img src="modules/EC_Flight_Bookings/assets/images/edit-infor-passenger.jpg" class="modal__product" />
            </div>
            <div class="modal__content">
                <h2 class="modal__title">Cập nhật thông tin hành khách</h2>
                <p class="direction"></p>
                <form>
                    <ul class="form-list">
                        <li class="form-list__row">
                            <label>Loại hành khách</label>
                            <div class="type__passenger" id="passenger_type"></div>
                        </li>
                        <li class="form-list__row">
                            <label>Họ và Tên</label>
                            <input type="text" class="" id="name_passenger" name="fullname" required="" />
                            <div class="name__error"></div>
                        </li>
                        <li class="form-list__row">
                            <label for="">Giới tính</label>
                            <div class="select-box">
                                <label class="select" for="slct">
                                    <select class="form-select not-radius-right" name="gender">
                                        <option value="Male">Nam</option>
                                        <option value="Female">Nữ</option>
                                    </select>
                                    <svg>
                                        <use xlink:href="#select-arrow-down"></use>
                                    </svg>
                                </label>
                                <!-- SVG Sprites-->
                                <svg class="sprites">
                                    <symbol id="select-arrow-down" viewbox="0 0 10 6">
                                        <polyline points="1 1 5 5 9 1"></polyline>
                                    </symbol>
                                </svg>
                            </div>
                        </li>
                        <li class="form-list__row form-list__row--inline">
                            <div>
                                <label>Ngày Sinh</label>
                                <div class="form-list__input-inline">
                                    <input type="text" class="only-numeric" id="cc_day" name="bd-day" placeholder="Ngày" minlength="1" maxlength="2" required="" />
                                    <input type="text" class="only-numeric" id="cc_month" name="bd-month" placeholder="Tháng" minlength="1" maxlength="2" required="" />
                                    <input type="text" class="only-numeric" id="cc_year" name="bd-year" placeholder="Năm" minlength="4" maxlength="4" required="" />
                                </div>
                                <div class="birthday__error"></div>
                            </div>
                        </li>
                        <li class="form-list__row--button">
                            <button type="button" class="button-modal mr-10" id="btn-update-passenger" data-dismiss="modal">Cập nhật</button>
                            <button type="button" class="button-modal close-modal btn-close-modal" data-dismiss="modal">Đóng</button>
                        </li>
                    </ul>
                    <input type="hidden" name="reservation_key">
                    <input type="hidden" name="passenger_key">
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-confirm-update-passenger">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Xác nhận cập nhật hành khách</h4>
            </div>
            <div class="modal-body">
                <p class="content">Phí cập nhật: <span id="edit-fee"></span></p>
                <p class="content">Bạn vẫn muốn tiến hành cập nhật lại thông tin hành khách ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary close-modal" id="confirm-update-passenger" data-dismiss="modal">Xác nhận</button>
                <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-confirm-payment-pnr">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-id-card-o me-3" aria-hidden="true"></i>Xác nhận thanh toán</h4>
            </div>
            <div class="modal-body">
                <p class="content">Thao tác sẽ trừ tiền vào tài khoản đại lý</p>
                <p class="content">Bạn có muốn thanh toán cho PNR <span id="pnr-payment-pnr"></span> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary close-modal" id="confirm-payment-pnr" data-dismiss="modal">Xác nhận</button>
                <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-payment-pnr-success">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Xuất vé thành công</h4>
            </div>
            <div class="modal-body">
                <div class="container info-payment">
                    <div class="row">
                        <div class="col-4 label">PNR</div>
                        <div class="col-1">:</div>
                        <div class="col-7 value" id="pnrtt" style="font-weight:bold;color:red"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 label">Khóa TT</div>
                        <div class="col-1">:</div>
                        <div class="col-7 value" id="khoatt"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 label">Biên lai TT</div>
                        <div class="col-1">:</div>
                        <div class="col-7 value" id="bienlaitt" style="font-weight:bold;color:red"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 label">Ngày TT</div>
                        <div class="col-1">:</div>
                        <div class="col-7 value" id="ngaytt" style="font-style:italic;"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 label">Phương thức TT</div>
                        <div class="col-1">:</div>
                        <div class="col-7 value" id="phuongthuctt"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 label">Mô tả</div>
                        <div class="col-1">:</div>
                        <div class="col-7 value" id="motatt"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 label">Ghi chú</div>
                        <div class="col-1">:</div>
                        <div class="col-7 value" id="ghichutt"></div>
                    </div>
                </div>
                <p class="recommend">Vui lòng lưu lại các thông tin trên để recheck sau này</p>
            </div>
            <div class="modal-footer">
                <button id="export-info-payment" class="btn btn-success btn-export_file">Xuất file .txt</button>
                <button type="button" class="btn btn-secondary btn-block close-modal" data-dismiss="modal" flag="">Đóng</button>
            </div>
        </div>
    </div>
</div>