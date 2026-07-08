<link rel="stylesheet" type="text/css" href="modules/EC_Bonus/css/calculate_bonus_by_source.css?v=2.0" />

<h1 class="title mb-0">Tính thưởng theo nguồn</h1>
<div class="box-section mt-2">
  <form action="index.php" method="get" name="frmCalculateBonusBySource" id="frmCalculateBonusBySource">
    <input type="hidden" name="module" value="EC_Bonus" />
    <input type="hidden" name="action" value="calculate_bonus_by_source" />
    <input type="hidden" name="source_type" value="{$SOURCE_TYPE_VALUE}" />
    <div class="d-flex align-items-center gap-4">
      <div class="d-flex gap-2 align-items-center">
        <span class="sublabel">Nguồn: </span>
        <input class="box-input" type="text" size="30" tabindex="103" value="{$SOURCE_NAME_VALUE}" id="source_name" name="source_name" autocomplete="off" placeholder="Mã booking, mã phiếu thu,..." required />
      </div>

      <input class="btn btn-primary" type="submit" name="btnRun" title="Tính thưởng" />
    </div>
  </form>

  {if isset($ERROR)}
    <div class="alert alert-danger mt-3">Tính thưởng thất bại: {$ERROR}</div>
  {elseif isset($NOT_FOUND)}
    <div class="alert alert-info mt-3">Không tìm thấy dữ liệu thưởng cho nguồn này (nguồn không tồn tại hoặc chưa đạt ngưỡng thưởng).</div>
  {elseif isset($SOURCE_BONUS)}
    <div class="table-responsive source-bonus-table-wrap mt-3">
      <table class="table table-bordered source-bonus-table">
        <thead>
          <tr>
            <th>Chỉ tiêu</th>
            <th>Công thức</th>
            <th>Giá trị</th>
          </tr>
        </thead>
        <tbody>
          <tr class="row-section">
            <td colspan="3"><strong>Thông tin nguồn</strong></td>
          </tr>
          <tr>
            <td>Nguồn</td>
            <td></td>
            <td>
              <a href="index.php?module={$SOURCE_BONUS.module}&action=DetailView&record={$SOURCE_BONUS.id}" target="_blank">{$SOURCE_BONUS.name}</a>
            </td>
          </tr>
          <tr>
            <td>Thời gian bay</td>
            <td></td>
            <td>{$SOURCE_BONUS.time}</td>
          </tr>
          <tr id="ct-1">
            <td>(1) Số vé</td>
            <td></td>
            <td>{$SOURCE_BONUS.ticket_qty}</td>
          </tr>

          <tr class="row-section">
            <td colspan="3"><strong>Doanh thu &amp; lợi nhuận</strong></td>
          </tr>
          <tr id="ct-2">
            <td>(2) Doanh thu</td>
            <td>Không tính chiết khấu</td>
            <td>{$SOURCE_BONUS.revenue}</td>
          </tr>
          <tr id="ct-3">
            <td>(3) Giá vốn</td>
            <td>Đã gồm phí xuất vé</td>
            <td>{$SOURCE_BONUS.cost}</td>
          </tr>
          <tr id="ct-4">
            <td>(4) Lợi nhuận</td>
            <td>(4) = <a class="ct-ref" href="#ct-2" title="(2) Doanh thu">(2)</a> &minus; <a class="ct-ref" href="#ct-3" title="(3) Giá vốn">(3)</a></td>
            <td>{$SOURCE_BONUS.profit}</td>
          </tr>
          <tr id="ct-5">
            <td>(5) Lợi nhuận bình quân / vé</td>
            <td>(5) = <a class="ct-ref" href="#ct-4" title="(4) Lợi nhuận">(4)</a> &divide; <a class="ct-ref" href="#ct-1" title="(1) Số vé">(1)</a></td>
            <td>{$SOURCE_BONUS.avg_profit}</td>
          </tr>

          <tr class="row-section">
            <td colspan="3"><strong>Ngưỡng &amp; tỷ lệ thưởng</strong></td>
          </tr>
          <tr id="ct-6">
            <td>(6) Ngưỡng thưởng tối thiểu</td>
            <td>Theo loại vé (quốc nội / quốc tế, một chiều / khứ hồi)</td>
            <td>{$SOURCE_BONUS.min_threshold}</td>
          </tr>
          <tr id="ct-7">
            <td>(7) Ngưỡng thưởng thêm</td>
            <td>Theo loại vé (quốc nội / quốc tế, một chiều / khứ hồi)</td>
            <td>{$SOURCE_BONUS.extra_threshold}</td>
          </tr>
          <tr id="ct-8">
            <td>(8) Tỷ lệ thưởng</td>
            <td>Theo nguồn khách hàng</td>
            <td>{$SOURCE_BONUS.bonus_percent}</td>
          </tr>
          <tr id="ct-9">
            <td>(9) Tỷ lệ thưởng thêm</td>
            <td>Theo nguồn khách hàng và lợi nhận bình quân</td>
            <td>{$SOURCE_BONUS.extra_bonus_percent}</td>
          </tr>

          <tr class="row-section">
            <td colspan="3"><strong>Kết quả thưởng</strong></td>
          </tr>
          <tr id="ct-10">
            <td>(10) Thưởng / vé</td>
            <td>(10) = <a class="ct-ref" href="#ct-6" title="(6) Ngưỡng thưởng tối thiểu">(6)</a> &times; <a class="ct-ref" href="#ct-8" title="(8) Tỷ lệ thưởng">(8)</a>
              <span class="text-primary">+ max(0, <a class="ct-ref" href="#ct-5" title="(5) Lợi nhuận bình quân / vé">(5)</a> &minus; <a class="ct-ref" href="#ct-7" title="(7) Ngưỡng thưởng thêm">(7)</a>) &times; <a class="ct-ref" href="#ct-9" title="(9) Tỷ lệ thưởng thêm">(9)</a>, chỉ tính khi <a class="ct-ref" href="#ct-5" title="(5) Lợi nhuận bình quân / vé">(5)</a> > <a class="ct-ref" href="#ct-7" title="(7) Ngưỡng thưởng thêm">(7)</a></span>
              {if !$SOURCE_BONUS.is_valid_zalo}<span class="text-danger"> &divide; 2 (Chưa vào Zalo)</span>{/if}
            </td>
            <td>{$SOURCE_BONUS.bonus_per_ticket}</td>
          </tr>
          <tr id="ct-11">
            <td>(11) Tổng thưởng</td>
            <td>
              (11) = <a class="ct-ref" href="#ct-10" title="(10) Thưởng / vé">(10)</a> &times; <a class="ct-ref" href="#ct-1" title="(1) Số vé">(1)</a>
            </td>
            <td>{$SOURCE_BONUS.total_bonus_calc}</td>
          </tr>
          <tr id="ct-12">
            <td>(12) Tổng thưởng trực tiếp</td>
            <td>(12) = <a class="ct-ref" href="#ct-11" title="(11) Tổng thưởng">(11)</a> &times; 70%</td>
            {if $IS_MANAGER}
              <td class="direct-bonus-trigger" title="Bấm để chia sẻ / điều chỉnh thưởng trực tiếp"
                  data-source-id="{$SOURCE_BONUS.id}" data-source-type="{$SOURCE_BONUS.module}" data-source-name="{$SOURCE_BONUS.name}">{$SOURCE_BONUS.total_direct_bonus}</td>
            {else}
              <td>{$SOURCE_BONUS.total_direct_bonus}</td>
            {/if}
          </tr>
          <tr id="ct-13">
            <td>(13) Tổng thưởng gián tiếp</td>
            <td>(13) = <a class="ct-ref" href="#ct-11" title="(11) Tổng thưởng">(11)</a> &minus; <a class="ct-ref" href="#ct-12" title="(12) Tổng thưởng trực tiếp">(12)</a></td>
            <td>{$SOURCE_BONUS.total_indirect_bonus}</td>
          </tr>
          <tr id="ct-14">
            <td>(14) Tổng KPI gián tiếp</td>
            <td>Tổng KPI các bước xử lý, không tính bước hoàn tất</td>
            <td>{$SOURCE_BONUS.total_indirect_kpi}</td>
          </tr>
          <tr id="ct-15">
            <td>(15) Thưởng gián tiếp / KPI</td>
            <td>(15) = <a class="ct-ref" href="#ct-13" title="(13) Tổng thưởng gián tiếp">(13)</a> &divide; <a class="ct-ref" href="#ct-14" title="(14) Tổng KPI gián tiếp">(14)</a></td>
            <td>{$SOURCE_BONUS.indirect_bonus_per_kpi}</td>
          </tr>
        </tbody>
      </table>
    </div>

    {if $IS_MANAGER}
      <div class="bonus-modal-overlay" id="direct_bonus_modal">
        <div class="bonus-modal">
          <div class="bonus-modal-header">Điều chỉnh thưởng trực tiếp</div>
          <div class="bonus-modal-body">
            <div class="bonus-modal-info">Nguồn: <strong class="bm-source"></strong></div>
            <div class="bonus-modal-info">Tối đa có thể chia: <strong class="bm-pool">{$BONUS_POOL_FMT}</strong></div>
            <div class="bonus-user-list">
              {foreach from=$BONUS_USERS item=bu}
                <div class="bonus-user-row">
                  <label class="bonus-user-name" for="direct_bonus_value_{$bu.id}">{$bu.name}</label>
                  <input type="text" id="direct_bonus_value_{$bu.id}" class="bonus-modal-input direct-bonus-input"
                         data-user-id="{$bu.id}" value="{$bu.direct_raw}" autocomplete="off" />
                </div>
              {/foreach}
            </div>
            <div class="bonus-modal-info">Đã chia: <strong class="bm-total">0</strong></div>
            <div class="bonus-modal-error"></div>
          </div>
          <div class="bonus-modal-footer">
            <button type="button" class="btn bm-cancel">Hủy</button>
            <button type="button" class="btn btn-primary bm-submit">Cập nhật</button>
          </div>
        </div>
      </div>
      <script type="text/javascript">
        var BONUS_DIRECT_POOL = {$BONUS_POOL};
      </script>
      <script type="text/javascript" src="modules/EC_Bonus/js/calculate_bonus_by_source.js?v=1.4"></script>
    {/if}
  {/if}
</div>
