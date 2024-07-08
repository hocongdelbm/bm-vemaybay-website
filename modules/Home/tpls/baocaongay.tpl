

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
	
	<input autocomplete="off" type="text" name="tungay" id="tungay" value="{$POST_TUNGAY}" title="" size="11" maxlength="10" />
	<img border="0" src="themes/default/images/jscalendar.gif" alt="Từ ngày" id="tungay_trigger" align="absmiddle" />
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
	÷
	<input autocomplete="off" type="text" name="denngay" id="denngay" value="{$POST_DENNGAY}" title="" size="11" maxlength="10" />
	<img border="0" src="themes/default/images/jscalendar.gif" alt="Đến ngày" id="denngay_trigger" align="absmiddle" />
	{literal}
	<script type="text/javascript">
		Calendar.setup ({
		inputField : "denngay",
		daFormat : "%d-%m-%Y",
		button : "denngay_trigger",
		singleClick : true,
		dateStr : "",
		step : 1,
		weekNumbers:false
	});
	</script>
	{/literal}
	<input type="submit" name="btnSearch" id="btnSearch" value="Xem" title="Xem" />
</form>

<table id="tbl_baocaongay" class="table-baocaongay table-details__booking" cellpadding="0" cellspacing="0" border="0">
	<tr>
    	<td width="28%" align="center" style="font-weight:bold;">
        	<img src="custom/themes/default/images/user_icon.jpg" />
            <br />Nhân viên
        </td>
        <td width="12%" align="center" style="font-weight:bold;">
        	<img src="custom/themes/default/images/task_icon_16x16.png" />
            <br />Công việc
        </td>
        <td width="12%" align="center" style="font-weight:bold;">
        	<img src="custom/themes/default/images/contact_icon.jpg" />
            <br />Liên hệ
        </td>
        <td width="12%" align="center" style="font-weight:bold;">
        	<img src="custom/themes/default/images/meeting_icon.jpg" />
            <br />Cuộc gặp
        </td>
        <td width="12%" align="center" style="font-weight:bold;">
        	<img src="custom/themes/default/images/callcenter_icon.jpg" />
            <br />Cuộc gọi
        </td>
        <td width="12%" align="center" style="font-weight:bold;">
        	<img src="custom/themes/default/images/opportunity_icon.jpg" />
            <br />Cơ hội
        </td>
    </tr>
    {$DATA}
</table>

<div id="viewDetailDialog" title="Chi tiết" style="display:none; font-family:arial; font-size:12px;">
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
    	<tr>
        	<td width="5%" align="center" style="font-weight:bold;">STT</td>
            <td width="20%" align="center" style="font-weight:bold;">Tên</td>
            <td width="15%" align="center" style="font-weight:bold;">Ngày tạo</td>
            <td width="30%" align="center" style="font-weight:bold;">Khách hàng</td>
            <td width="30%" align="center" style="font-weight:bold;">Diễn giải</td>
        </tr>
    </table>
</div>
</div>