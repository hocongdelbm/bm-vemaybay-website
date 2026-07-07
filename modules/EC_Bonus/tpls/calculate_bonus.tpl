<h1 class="title">Tính thưởng booking</h1>

<div class="box-section">
  <form action="index.php" method="get" name="frmCalculateBonus" id="frmCalculateBonus">
    <input type="hidden" name="module" value="EC_Bonus" />
    <input type="hidden" name="action" value="calculate_bonus" />
    <div class="d-flex align-items-center gap-4">
      <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
        <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
          <span class="sublabel">Từ ngày: </span>
          <div class="dateTime d-flex gap-2 position-relative">
            <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off" required>
            <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
              </svg>
            </button>
            {literal}
            <script type="text/javascript">
              Calendar.setup({
                inputField : "from_date",
                daFormat : "%d-%m-%Y",
                button : "fdate_trigger",
                singleClick : true,
                dateStr : "",
                step : 1
              });
            </script>
            {/literal}
          </div>
        </div>

        <svg width="40" height="20" fill="none">
          <g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M33.5 8.5L36 11M4 11h32"></path>
          </g>
          <defs>
            <clipPath id="icon_arrow_flight_long_svg__clip0">
              <path fill="#fff" d="M0 0h40v20H0z"></path>
            </clipPath>
          </defs>
        </svg>

        <div class="d-flex gap-2 align-items-center date_trigger--wrap tdate_trigger--wrap">
          <span class="sublabel">Đến ngày: </span>
          <div class="dateTime d-flex gap-2 position-relative">
            <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="104" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off" required>
            <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
              </svg>
            </button>
            {literal}
            <script type="text/javascript">
              Calendar.setup({
                inputField : "to_date",
                daFormat : "%d-%m-%Y",
                button : "tdate_trigger",
                singleClick : true,
                dateStr : "",
                step : 2
              });
            </script>
            {/literal}
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <input type="checkbox" class="form-check-input" id="save_records" name="save_records" value="1" {$SAVE_CHECKED} />
        <label class="sublabel" for="save_records" style="margin-bottom:0; cursor:pointer;">Lưu kết quả</label>
      </div>

      <input class="btn btn-primary" type="submit" name="btnRun" value="Tính thưởng" title="Tính thưởng" />
    </div>
  </form>

  {if isset($ERROR)}
    <div class="alert alert-danger mt-3">Tính thưởng thất bại: {$ERROR}</div>
  {elseif isset($BONUS_REPORT)}
    {if $BONUS_REPORT.grand.users == 0}
      <div class="alert alert-info mt-3">Không có dữ liệu thưởng trong khoảng thời gian đã chọn.</div>
    {else}
      <div class="alert alert-success mt-3">
        Đã tính thưởng cho {$BONUS_REPORT.grand.users} nhân viên trên {$BONUS_REPORT.grand.bookings} lượt booking.
        {if $SAVED}Kết quả đã được lưu vào Thưởng booking.{/if}
      </div>
      <div class="table-responsive mt-3" style="overflow-x:auto;">
        <table class="table table-bordered" style="width:100%">
          <thead>
            <tr>
              <th>Nhân viên</th>
              <th>Booking</th>
              <th style="text-align:right">KPI</th>
              <th style="text-align:right">Thưởng trực tiếp</th>
              <th style="text-align:right">Thưởng gián tiếp</th>
              <th style="text-align:right">Tổng thưởng</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$BONUS_REPORT.users item=user}
              {foreach from=$user.bookings item=bk name=ubk}
                <tr>
                  {if $smarty.foreach.ubk.first}
                    <td rowspan="{$user.bookings|@count}"><strong>{$user.name}</strong></td>
                  {/if}
                  <td>
                    <a href="index.php?module=EC_Flight_Bookings&action=DetailView&record={$bk.id}" target="_blank">{$bk.name}</a>
                  </td>
                  <td style="text-align:right">{$bk.kpi}</td>
                  <td style="text-align:right">{$bk.direct}</td>
                  <td style="text-align:right">{$bk.indirect}</td>
                  <td style="text-align:right">{$bk.total}</td>
                </tr>
              {/foreach}
              <tr style="background:#f6f6f6">
                <td colspan="2"><strong>Tổng của {$user.name}</strong></td>
                <td style="text-align:right"><strong>{$user.kpi}</strong></td>
                <td style="text-align:right"><strong>{$user.direct}</strong></td>
                <td style="text-align:right"><strong>{$user.indirect}</strong></td>
                <td style="text-align:right"><strong>{$user.total}</strong></td>
              </tr>
            {/foreach}
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"><strong>TỔNG CỘNG</strong></td>
              <td style="text-align:right"><strong>{$BONUS_REPORT.grand.kpi}</strong></td>
              <td style="text-align:right"><strong>{$BONUS_REPORT.grand.direct}</strong></td>
              <td style="text-align:right"><strong>{$BONUS_REPORT.grand.indirect}</strong></td>
              <td style="text-align:right"><strong>{$BONUS_REPORT.grand.total}</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
    {/if}
  {/if}
</div>
