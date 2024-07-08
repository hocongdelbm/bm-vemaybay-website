{literal}
    <style>
        h1.title {
            background: url(custom/themes/default/images/employee_16x16.png) left center no-repeat;
            margin-bottom: 10px;
            padding-left: 20px;
        }

        table.data-list {
            margin-top: 10px;
            width: 100%;
            font-size: 12px;
            line-height: 15px;
            border-collapse: collapse;
        }

        table.data-list tr:first-child td {
            text-align: center;
            font-weight: bold;
            background: #eee;
        }

        table.data-list tr:last-child td {
            font-weight: bold;
            background: #eee;
        }

        table.data-list tr td {
            padding: 5px;
            border: 1px solid #ccc;
        }

        table.data-list tr td.number {
            text-align: right;
        }
    </style>
    <script>
    </script>
{/literal}
<h1 class="title">BẢNG XẾP HẠNG NHÂN VIÊN</h1>
<form action="index.php" method="post" name="frmViewReport" id="frmViewReport">
    <input type="hidden" name="module" value="EC_Flight_Bookings"/>
    <input type="hidden" name="action" value="employeereport"/>
    Từ ngày <input type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE}"
                   id="from_date" name="from_date" autocomplete="off">
    <img border="0" align="absmiddle" id="fdate_trigger" alt="input date"
         src="themes/default/images/jscalendar.gif">
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
     - Đến ngày <input type="text" maxlength="10" size="11" tabindex="103" title="" value="{$TO_DATE}"
                    id="to_date" name="to_date" autocomplete="off">
    <img border="0" align="absmiddle" id="tdate_trigger" alt="input date"
         src="themes/default/images/jscalendar.gif">
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
     <input class="button" type="submit" id="btnViewReport" name="btnViewReport" title="Xem báo cáo" value="Xem báo cáo">
     <input class="button" type="submit" id="btnExportExcel" name="btnExportExcel" title="Xuất excel" value="Xuất excel">
</form>

<table class="data-list">
    <tr>
        <td width="2%">STT</td>
        <td width="24%">Họ tên</td>
        <td width="10%">Chức vụ</td>
        <td width="10%">Được giao</td>
        <td width="10%">Hoàn tất</td>
        <td width="10%">% Hiệu suất</td>
        <td width="10%">KPI</td>
        <td width="12%">Số lượng vé</td>
        <td width="12%">Doanh số</td>
    </tr>
    {$DATA}
</table>
