<link type="text/css" rel="stylesheet" href="modules/EC_Flight_Bookings/css/issueticket.css?v=1.0">

<div class="container">
    <header class="booking-header">
        <h3>XUẤT VÉ</h3>
        <div class="box-frm__wrap">
            <form method="post" action="index.php" name="frmIssueTicket" id="frmIssueTicket">
                <select name="airlineCode" class="form-select" style="width:210px;">
                    <option value="VJ">VJ (Vietjet Air)</option>
                    <option value="VN">VN (Vietnam Airlines)</option>
                    <option value="QH">QH (Bamboo Airways)</option>
                    <option value="VU">VU (Vietravel)</option>
                </select>
                <div class="wrap-input">
                    <input type="text" name="pnr" class="form-control" placeholder="Nhập PNR">
                    <button type="button" class="btn" id="btnSearch">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </header>

    <div id="bookingContent" class="booking-content hidden">
        <input type="hidden" name="systemCode" value="" />
        <input type="hidden" name="bookingCode" value="" />

        <!-- Booking Information Section -->
        <section class="booking-info-section">
            <h2>
                Thông tin booking
                <div class="expiry-badge" id="expiryBadge" style="display: none;"></div>
            </h2>
            <div class="info-grid">
                <div class="info-card">
                    <h3>
                        Chi tiết
                        <div class="status-badges">
                        <span class="status-badge" id="paidBadge" data-tooltip="Payment Status Description"></span>
                        <span class="status-badge" id="voidBadge" data-tooltip="Void Status Description"></span>
                        <span class="status-badge" id="refundBadge" data-tooltip="Refund Status Description"></span>
                        <span class="status-badge" id="editBadge" data-tooltip="Edit Status Description"></span>
                    </div>
                    </h3>
                    <div class="info-row">
                        <span class="label">Booking Code (PNR):</span>
                        <span class="value" id="bookingCode"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Tình trạng:</span>
                        <span class="value status" id="bookingStatus"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Tổng giá mua:</span>
                        <span class="value amount" id="totalAmount"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Đã thanh toán:</span>
                        <span class="value amount paid-amount" id="paidAmount"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Chưa thanh toán:</span>
                        <span class="value amount unpaid-amount" id="unpaidAmount"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngày giờ đặt:</span>
                        <span class="value" id="bookingDate"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Hạn giữ chỗ:</span>
                        <span class="value" id="bookingExpiry"></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>Liên hệ</h3>
                    <div class="info-row">
                        <span class="label">Họ tên:</span>
                        <span class="value" id="contactName"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email:</span>
                        <span class="value" id="contactEmail"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">SĐT:</span>
                        <span class="value" id="contactPhone"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Địa chỉ:</span>
                        <span class="value" id="contactAddress"></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Flight Itinerary Section -->
        <section class="flights-section">
            <h2>Thông tin hành trình</h2>
            <div id="flightsList" class="flights-list"></div>
        </section>

        <!-- Passengers Section -->
        <section class="passengers-section">
            <h2>Thông tin hành khách</h2>
            <div class="table-container">
                <table id="passengersTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Loại</th>
                            <th>Giới tính</th>
                            <th>Ngày sinh</th>
                            <th>Liên hệ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- Fare Breakdown Section -->
        <section class="fare-section">
            <h2>Thông tin giá vé</h2>
            <div class="table-container">
                <table id="fareTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Loại HK</th>
                            <th>Giá vé</th>
                            <th>Phí sân bay</th>
                            <th>Phí khác</th>
                            <th>VAT</th>
                            <th>Tổng</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <p class="mt-2 ms-2"><i>Đây là thông tin giá vé trên mỗi loại hành khách (Nhân số lượng để ra số tổng)</i></p>
        </section>
    </div>

    <!-- New Payment Button Section -->
    <section class="payment-section" id="paymentSection" style="display: none;">
        <div class="payment-container">
            <div class="payment-info">
                <h3>Thanh toán Booking</h3>
                <div class="payment-details">
                    <div class="payment-row">
                        <span class="label">Tổng giá mua:</span>
                        <span class="amount" id="paymentTotalAmount"></span>
                    </div>
                    <div class="payment-row expiry-info">
                        <span class="label">Cần thanh toán:</span>
                        <span class="amount unpaid" id="paymentUnpaidAmount"></span>
                    </div>
                    <!-- <div class="payment-row expiry-info">
                        <span class="label">Hạn thanh toán:</span>
                        <span class="expiry-time" id="paymentDeadline"></span>
                    </div> -->
                </div>
            </div>
            <div class="payment-actions">
                <button id="payNowButton" class="btn-pay-now">
                    <span class="btn-icon">💳</span>
                    <span class="btn-text">Pay Now</span>
                    <span class="btn-amount" id="payButtonAmount"></span>
                </button>
                <button id="cancelBookingButton" class="btn-cancel">
                    Hủy Booking
                </button>
            </div>
        </div>
    </section>
</div>

<script src="modules/EC_Flight_Bookings/js/api_phuongnam/issueticket.js?v=1.0"></script>