

{literal}
    <script>
		$(document).ready(function(){
			$('.view_detail').on('click',function(){
				var uid = $(this).attr('uid');
				var module = $(this).attr('module');
				var fdate = $('#tungay').val();
				var tdate = $('#denngay').val();
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
				url: 'index.php?module=Home&entryPoint=entryPointGetReportData&action=baocaongay',
				success: function(output){
					json_data = output;
				}
			});
			return json_data;
		}
		function loadReportData(json_data, module){
			var data = $.parseJSON(json_data);
			var html = '';
			var i = 0;
			$.each(data, function(i,item){
				html += '<tr>';
				html += '<td align="center">'+ (i+1) +'</td>';
				html += '<td align="left"><a href="index.php?module='+ module +'&action=DetailView&record='+ data[i].id +'" target="_blank" title="Xem chi tiết">'+ data[i].name +'</a></td>';
				html += '<td align="center">'+ data[i].date_entered +'</td>';
				html += '<td align="left">'+ (data[i].account_name == null ? '&nbsp;' : data[i].account_name) +'</td>';
				html += '<td align="left">'+ (data[i].description == null ? '&nbsp;' : data[i].description) +'</td>';
				html += '</tr>';
				i++;
			});
			return html;
		}
    </script>
{/literal}

<h1 class="title">Báo cáo ngày</h1>

<div class="box-section">
	<form action="index.php" method="post" name="frmSearch" id="frmSearch">
	<input type="hidden" name="module" value="Home" />
		<input type="hidden" name="action" value="baocaongay" />
		
        <div class="from-to-date--wrap d-inline-flex gap-3 align-items-center">
			<div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                <span class="sublabel">Từ ngày: </span>    
                <div class="dateTime d-flex gap-2 position-relative">
                    <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_TUNGAY}" id="tungay" name="tungay" autocomplete="off">
                    <button class="icon_dateTime" type="button" id="tungay_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                          </svg>
                    </button>
                    {literal}
                        <script type="text/javascript">
                                Calendar.setup ({
                                    inputField : "tungay",
                                    daFormat : "%d-%m-%Y",
                                    button : "tungay_trigger",
                                    singleClick : true,
                                    dateStr : "",
                                    step : 1,
									weekNumbers:false
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

			<div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
                <span class="sublabel">Đến ngày: </span>    
                <div class="dateTime d-flex gap-2 position-relative">
                   <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$POST_DENNGAY}" id="denngay" name="denngay" autocomplete="off">
                    <button class="icon_dateTime" type="button" id="denngay_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                        </svg>
                    </button>
                    {literal}
                        <script type="text/javascript">
                                Calendar.setup ({
                                    inputField : "denngay",
                                    daFormat : "%d-%m-%Y",
                                    button : "denngay_trigger",
                                    singleClick : true,
                                    dateStr : "",
                                    step : 1
                                });
                        </script>
                    {/literal}
                </div>
            </div>
		</div>

		<input type="submit" class="btn btn-primary" name="btnSearch" id="btnSearch" value="Xem" title="Xem" />
	</form>

	<table id="tbl_baocaongay" class="table-baocaongay table-details__booking mt-3" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td align="center" class="fw-bold">
				Nhân viên
			</td>
			<td width="12%" align="center" class="fw-bold">
				Công việc
			</td>
			<td width="12%" align="center" class="fw-bold">
				Liên hệ
			</td>
			<td width="12%" align="center" class="fw-bold">
				Cuộc gặp
			</td>
			<td width="12%" align="center" class="fw-bold">
				Cuộc gọi
			</td>
			<td width="12%" align="center" class="fw-bold">
				Cơ hội
			</td>
		</tr>
		{$DATA}
	</table>

	<div id="viewDetailDialog" title="Chi tiết" style="display:none; font-family:arial; font-size:12px;">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td width="5%" align="center">STT</td>
				<td width="20%" align="center">Tên</td>
				<td width="15%" align="center">Ngày tạo</td>
				<td width="30%" align="center">Khách hàng</td>
				<td align="center">Diễn giải</td>
			</tr>
		</table>
	</div>
</div>