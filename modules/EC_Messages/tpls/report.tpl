<div class="report container mt-3">
    <h3 class="title">Báo cáo chi phí</h3>
    <div class="search">
        <form id="form-search" method="GET" action="/index.php" >
            <input type="hidden" name="module" value="EC_Messages" />
            <input type="hidden" name="action" value="report" />
            <select name="period" class="form-select">
                <option value="today">Hôm nay</option>
                <option value="yesterder">Hôm qua</option>
                <option value="7days">7 ngày</option>
                <option value="30days">30 ngày</option>
                <option value="90days">90 ngày</option>
                <option value="year">1 năm</option>
                <option value="other">Khác (Chọn ngày)</option>
            </select>
            <div class="wrap-choose-date">
                <b class="label">Từ</b>
                <input type="date" name="from_date" id="from_date" class="form-control input-date" />
                <b class="label">đến</b>
                <input type="date" name="to_date" id="to_date" class="form-control input-date" />
                <button type="submit" id="view_report" class="btn btn-primary ms-2">Xem</button>
            </div>
        </form>
    </div>
    <div>
        <table class="table table-hover table-cost">
            <thead>
                <tr class="table-primary">
                    <th>Loại</th>
                    <th>Số lượng</th>
                    <th>Chi phí</th>
                </tr>
            </thead>
            <tbody>
                {$TBODY}
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">Tổng</th>
                    <th class="total-cost">{$TOTAL_COST}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-4">
        <h5 class="subtitle">Web Portal kiểm tra chiến dịch tin nhắn</h5>
        <p><a href="https://portal.worldsms.vn/login" target="_blank">portal.worldsms.vn</a></p>
        <p><b>Username:</b> snext_travelqc</p>
        <p><b>Password:</b> Wa876cWv</p>
    </div>
</div>