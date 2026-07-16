<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/view_assignbk.css?v={$VERSION}">

<div class="d-flex align-items-center gap-3 mb-2">
    <h1 class="title title-online_tbl mb-0">Danh sách Online / Offline</h1>
    {if $IS_ALLOWED_USER}
    <button id="btn_update_online" class="btn btn-outline-primary btn-sm">Cập nhật</button>
    {/if}
</div>

<div id="online_report" class="box-section">
    {$ONLINE_DATA}
</div>


<div class="online-rules">
    <div class="online-rules__title">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>
        Quy tắc Online / Busy / Offline
    </div>
    <ul class="online-rules__list">
        <li><span class="dot dot-online"></span><b>Online</b>: đang trong hàng chờ luân phiên. Booking được giao cho người Online <b>đầu hàng</b>; nhận xong tự xuống <b>cuối hàng</b>.</li>
        <li><span class="dot dot-busy"></span><b>Busy</b>: tạm bận — <b>không nhận booking mới</b>, chỉ được giao khi không còn ai Online. <b>Không</b> nhận cuộc gọi</li>
        <li><span class="dot dot-offline"></span><b>Offline</b>: không nhận booking và không nhận cuộc gọi.</li>
        <li><b>Đăng nhập BM</b> = tự động Online (xếp cuối hàng); <b>Đăng xuất</b> = Offline.</li>
        <li>Được giao booking mà <b>quá 5 phút chưa xử lý</b> → hệ thống tự chuyển <b>Offline</b> và giao lại cho người khác.</li>
        <li>Trạng thái này đồng bộ với <b>tổng đài</b>: chỉ khi Online mới nhận cuộc gọi, <b>Busy/Offline</b> sẽ ngưng.</li>
    </ul>
    {if $IS_ALLOWED_USER}
    <ul class="online-rules__list online-rules__admin">
        <li><b>UP</b>: đưa user lên <b>đầu hàng</b> Online (được giao booking kế tiếp).</li>
        <li><b>DOWN</b>: đưa xuống <b>cuối hàng</b> Online.</li>
        <li><b>BUSY</b>: chuyển sang Busy · <b>OFF</b>: chuyển Offline · <b>DEL</b>: xoá khỏi bảng hôm nay.</li>
        <li>Mọi nút trên đều đồng bộ luôn <b>trạng thái agent + tổng đài</b> của user đó.</li>
    </ul>
    {/if}
</div>

<script src="modules/EC_Flight_Bookings/js/view_assignbk.js?v={$VERSION}"></script>
