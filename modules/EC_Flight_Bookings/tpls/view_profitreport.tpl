

{literal}
    <script>
		$(document).ready(function(){
			$('.view_detail').on('click',function(){
				var uid     = $(this).attr('uid');
				var module  = $(this).attr('module');
				var fdate   = $('#from_date').val();
				var tdate   = $('#to_date').val();

				$('#viewDetailDialog').dialog({
					height: 480,
					width: 900,
					modal: true
				});

				var data = getReportData(module, uid, fdate, tdate);

				$("#viewDetailDialog table").find("tr:gt(0)").remove();
				if(data != 0){
					$('#viewDetailDialog table').append(loadReportData(data, module));
				}
                
			});
		});

		function getReportData(module, uid, fdate, tdate){
			var json_data;
			$.ajax({	
				cache: false,
				type: 'post',
				data: 'module='+ module +'&uid=' + uid + '&fdate='+ fdate +'&tdate=' + tdate,
				async: false,
				url: 'index.php?entryPoint=entryPointGetReportData',
				success: function(output){
					json_data = output;
				}
			});
			return json_data;
		}

		function loadReportData(json_data, module){
			var data    = $.parseJSON(json_data);
			var html    = '';
			var i       = 0;

			$.each(data, function(i,item){
				html += '<tr>';
				html += '<td align="center">'+ (i+1) +'</td>';
				html += '<td align="left"><a href="index.php?module='+ module +'&action=DetailView&record='+ data[i].id +'" target="_blank" title="Xem chi tiết">'+ data[i].name +'</a></td>';
				html += '<td align="center">'+ data[i].date_entered +'</td>';
				html += '<td align="right">'+ data[i].amount +'</td>';
				html += '<td align="left">'+ (data[i].account_name == null ? '&nbsp;' : data[i].account_name) +'</td>';
				html += '<td align="left">'+ (data[i].description == null ? '&nbsp;' : data[i].description) +'</td>';
				html += '</tr>';
				i++;
			});

			return html;
		}

        $(document).on('change', '#date_select', function(event) {
            $("#from_date").val($(this).find("option:selected").attr("fromdate"));
            $("#to_date").val($(this).find("option:selected").attr("todate"));
        });
    </script>
{/literal}

<h1 class="title">BÁO CÁO LÃI LỖ</h1>

<div class="box-section">
<form action="index.php" method="post" name="frmSearch" id="frmSearch">
    <input type="hidden" name="module" value="EC_Flight_Bookings" />
    <input type="hidden" name="action" value="profitreport" />
    
    <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center mb-3">
        <select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>

        <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
            <span class="text-label">Từ ngày: </span>    
            <div class="dateTime d-flex gap-2 position-relative">
                <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
                <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                        </svg>
                </button>
                {literal}
                    <script type="text/javascript">
                        Calendar.setup({
                                    inputField: "from_date",
                                    daFormat: "%d-%m-%Y",
                                    button: "fdate_trigger",
                                    singleClick: true,
                                    dateStr: "",
                                    step: 1
                                }
                        );
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

        <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
            <span class="text-label">Đến ngày: </span>    
            <div class="dateTime d-flex gap-2 position-relative">
                <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
                <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                        </svg>
                </button>
                {literal}
                    <script type="text/javascript">
                        Calendar.setup({
                                    inputField: "to_date",
                                    daFormat: "%d-%m-%Y",
                                    button: "tdate_trigger",
                                    singleClick: true,
                                    dateStr: "",
                                    step: 2
                                }
                        );
                    </script>
                {/literal}
            </div>
        </div>

        <input type="submit" id="btnSearch" name="btnSearch" class="btn btn-primary" value="Xem" title="Xem"/>
    </div>

    <table class="table-profitreport table-details__booking" border="0" cellpadding="0" cellspacing="0" id="profit_list">
        <thead>
            <tr>
                <th width="5%" align="center">STT</th>
                <th width="15%" align="center">Tên mục</th>
                <th width="15%" align="center">Số tiền</th>
                <th width="15%" align="center">Tháng trước</th>
                <th width="15%" align="center">% tháng trước</th>
                <th width="15%" align="center">Quý này</th>
                <th width="15%" align="center">% quý này</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center">1</td>
                <td align="left">Doanh thu</td>
                <td align="right">{$TOTAL_AMOUNT}</td>
                <td align="right">{$TOTAL_AMOUNT_PREV_MONTH}</td>
                <td align="right">{$TOTAL_AMOUNT_PREV_MONTH_PERCENT}</td>
                <td align="right">{$TOTAL_AMOUNT_CURR_QUARTER}</td>
                <td align="right">{$TOTAL_AMOUNT_CURR_QUARTER_PERCENT}</td>
            </tr>
            
            <tr>
                <td align="center">2</td>
                <td align="left">Giá mua</td>
                <td align="right">{$TOTAL_BOUGHT_AMOUNT}</td>
                <td align="right">{$TOTAL_BOUGHT_AMOUNT_PREV_MONTH}</td>
                <td align="right">{$TOTAL_BOUGHT_AMOUNT_PREV_MONTH_PERCENT}</td>
                <td align="right">{$TOTAL_BOUGHT_AMOUNT_CURR_QUARTER}</td>
                <td align="right">{$TOTAL_PROFIT_CURR_QUARTER_PERCENT}</td>
            </tr>
            
            <tr>
                <td align="center" class="bg-yellow fw-semibold">3</td>
                <td align="left" class="bg-yellow fw-semibold">Doanh số</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_PREV_MONTH}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_PREV_MONTH_PERCENT}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_CURR_QUARTER}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_CURR_QUARTER_PERCENT}</td>
            </tr>
            
            {$DATA}
            
            <tr>
                <td align="center" class="bg-yellow fw-semibold">{$LAST_ROW_NUM}</td>
                <td align="left" class="bg-yellow fw-semibold">Lợi nhuận</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_FINAL}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_FINAL_PREV_MONTH}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_FINAL_PREV_MONTH_PERCENT}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_FINAL_CURR_QUARTER}</td>
                <td align="right" class="bg-yellow fw-semibold">{$TOTAL_PROFIT_FINAL_CURR_QUARTER_PERCENT}</td>
            </tr>

            {$OTHER_AMT}

        </tbody>
    </table>
</form>

<div id="viewDetailDialog" title="Chi tiết" style="display:none;">
	<table class="table-viewDetailDialog table-details__booking" cellspacing="0" cellpadding="0" border="0">
        <thead>
            <tr>
                <th width="5%" align="center">STT</th>
                <th width="15%" align="center">Số chứng từ</th>
                <th width="12%" align="center">Ngày ghi sổ</th>
                <th width="15%" align="center">Số tiền</th>
                <th width="18%" align="center">Người nhận</th>
                <th width="35%" align="center">Diễn giải</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

</div>
