{literal}
<style>
.invoice-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 20px;
}
.invoice-box h3 {
    margin: 0 0 10px;
    font-size: 16px;
    border-bottom: 1px solid #eee;
    padding-bottom: 6px;
}
.invoice-table {
    width: 100%;
    border-collapse: collapse;
}
.invoice-table th,
.invoice-table td {
    padding: 8px;
    border-bottom: 1px solid #eee;
}
.status {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}
.status-0 { background:#e4e7ec; color:#374151; }
.status-1 { background:#dbeafe; color:#1e40af; }
.status-2 { background:#dcfce7; color:#166534; }
.message {color:#000;}
</style>
{/literal}

{if $REPORT_REMOVED_INV|@count > 0 || $REPORT_SIGNED_INV|@count > 0}
<div class="invoice-box">
    {if $COUNT_REMOVED_IN_INV > 0}
        <h3 class="mt-1 mb-3 text-danger">Đã xóa {$COUNT_REMOVED_IN_INV} số vé chưa tạo HĐ đầu ra</h3>
    {/if}

    <h3>Xử lý HĐ đầu ra theo đầu vào đã xóa</h3>
    <table class="invoice-table">
        <tr>
            <th>Số hóa đơn</th>
            <th>Tình trạng</th>
            <th></th>
        </tr>

        {foreach from=$REPORT_SIGNED_INV key=id item=row}
            <tr>
                <td>
                    <strong>
                        <a href="index.php?module=EC_HoaDonBan&action=DetailView&record={$id}" target="_blank">{$row.name|escape}</a>
                    </strong>
                </td>
                <td>
                    <span class="status status-2">Đã ký</span>
                </td>
                <td>
                    <span class="message">Giữ nguyên dữ liệu</span>
                </td>
            </tr>
        {/foreach}

        {foreach from=$REPORT_REMOVED_INV key=id item=row}
            <tr>
                <td>
                    <strong>{$row.name|escape}</strong>
                </td>
                <td>
                    <span class="status status-{$row.status}">
                        {if $row.status == '0'}Mới tạo
                        {elseif $row.status == '1'}Ghi sổ
                        {/if}
                    </span>
                </td>
                <td>
                    {if isset($row.message)}
                        <span class="text-danger">{$row.message|escape}, đã xóa dữ liệu <b>({$row.count_in} đầu vào)</b></span>
                    {else}
                        <span class="text-danger">Đã xóa dữ liệu <b>({$row.count_in} đầu vào)</b></span>
                    {/if}
                </td>
            </tr>
        {/foreach}
    </table>
</div>
{/if}

<center class="mt-2">
    <a class="btn btn-secondary" href="index.php?module=EC_HoaDonBan&action=inputinvoice&return_module=EC_Input_Invoices&return_action=DetailVie">Về trang danh sách</a>
</center>