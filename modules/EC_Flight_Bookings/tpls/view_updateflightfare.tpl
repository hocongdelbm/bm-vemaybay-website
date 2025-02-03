<h1 id="report-title" class="title">Cập nhật giá vé hãng Vietjet</h1>
<div class="container">
    <form id="update-flight-fare">
        <div>
            <label for="depCode">Nơi đi</label><br>
            <input type="text" name="depCode" id="depCode" value="" placeholder="SGN" maxlength="3" size="3" />
        </div>
        <div>
            <label for="arvCode">Nơi đến</label><br>
            <input type="text" name="arvCode" id="arvCode" value="" placeholder="HAN" maxlength="3" size="3" />
        </div>
        <div>
            <label for="depDate">Ngày đi</label><br>
            <input type="date" name="depDate" id="depDate" value="" />
        </div>
        <div>
            <label for="retDate">Ngày về</label><br>
            <input type="date" name="retDate" id="retDate" value="" />
        </div>
        <div>
            <button type="button" id="update_fare" class="btn btn-primary">Cập nhật giá mới</button>
        </div>
    </form>
</div>