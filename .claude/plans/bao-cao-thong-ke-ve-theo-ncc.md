# Plan: Báo cáo Thống kê vé theo Nhà cung cấp (NCC)

## Context

Team vận hành (Thu Nguyen, Quỳnh Trang) yêu cầu bổ sung khả năng thống kê vé **theo Nhà cung cấp (NCC)** — hiện tại báo cáo "Thống kê vé" (`bkagent`) chỉ gom **theo hãng bay**. Nhu cầu cụ thể trích từ trao đổi nội bộ:

1. *"Hiện tại đang thống kê theo hãng, giờ thêm phần xem chi tiết theo NCC nữa, tức có phần chọn thêm nhà cung cấp như Hồng Ngọc Hà, Phương Nam theo từng hãng."*
2. *"Nhưng chỗ này theo ngày xuất vé ạ… mình cũng dựa ngày phát sinh."*
3. *"Sẽ lấy theo công nợ phải trả, chứ không gom theo booking (tức tất cả phát sinh ghi nhận hết vào ngày hoàn tất) như hiện tại."*

**Vấn đề với báo cáo `bkagent` hiện tại:** số tiền được tính qua `calculateBKAmt($bk_id)` ở **cấp booking**, dồn toàn bộ giá trị vào **một** `date_ticket_issue` của booking. Điều này sai với yêu cầu công nợ phải trả NCC, nơi mỗi chặng (đi/về) có thể khác NCC và khác ngày xuất vé.

**Kết quả mong muốn:** một báo cáo mới, chọn NCC → xem tách theo từng hãng, số tiền lấy theo **mô hình công nợ phải trả** (`total_bought_price` ở cấp dòng `ec_booking_details`, gom theo `supplier_id`, ghi nhận theo ngày xuất vé của **từng chặng**).

## Quyết định thiết kế (đã chốt với user)

- **Vị trí:** view mới độc lập trong module `EC_Flight_Bookings`, action `bksupplier`. Không đụng vào `bkagent`.
- **Bố cục:** dropdown chọn NCC (Hồng Ngọc Hà, Phương Nam…) + bảng tổng hợp mỗi dòng = 1 NCC; chọn/bấm NCC hiện bảng chi tiết tách theo từng hãng bay.
- **Cột tiền:** trọng tâm **Giá mua = công nợ phải trả** (`SUM(total_bought_price)`), kèm SL vé, Chiết khấu (`supplier_discount`), Phí xuất vé (`fee_bought`), Giá bán dòng (`total_price`).

## Mô hình dữ liệu (tham chiếu — không cần đổi schema)

- NCC = record trong `accounts` với `account_type = 'Supplier' AND is_stop_tracking = 0`. Nhãn hiển thị: `ticker_symbol` (mã) + `name` (tên).
- Gắn NCC vào dòng vé qua `ec_booking_details.supplier_id` (chặng đi) — field `relate` tới Accounts, có index `idx_bkd_supplier` ([modules/EC_Booking_Details/vardefs.php:256-284](../../modules/EC_Booking_Details/vardefs.php)).
- Ngày xuất vé nằm ở `ec_flight_bookings`: `date_ticket_issue` (chặng đi) và `date_ticket_inbound_issue` (chặng về). **Không** có trên `ec_booking_details`.
- **Ghi nhận ngày theo từng chặng** — tái sử dụng đúng pattern của `view.debtopay.php`:
  `IF(d.direction = 0, p.date_ticket_issue, p.date_ticket_inbound_issue) AS posted_date`
  ([view.debtopay.php:601](../../modules/EC_Flight_Bookings/views/view.debtopay.php)).
- Điều kiện lọc booking hợp lệ (giống debtopay): `p.booking_status IN ('7','8') AND p.is_ticket_exported = 1 AND d.deleted = 0`.

## Các file cần tạo / sửa

### 1. `modules/EC_Flight_Bookings/controller.php` (sửa)
Thêm case mới vào switch (sau case `bkagent`, ~dòng 66):
```php
case "bksupplier":
    $this->action = "bksupplier";
    break;
```

### 2. `modules/EC_Flight_Bookings/Menu.php` (sửa)
Thêm menu item trong block `isManagerUser(...)` (cạnh "Thống kê vé", sau ~dòng 49):
```php
$module_menu[] = [
    "index.php?module=EC_Flight_Bookings&action=bksupplier&return_module=EC_Flight_Bookings&return_action=bksupplier",
    "Thống kê vé theo NCC",
    "bkagent",
    'EC_Flight_Bookings'
];
```
Cân nhắc đặt trong nhánh `ACLController::checkAccess('Bugs','edit',true)` như `debtopay`/`agentreport` (báo cáo công nợ). Nếu muốn rộng quyền hơn thì để chung với "Thống kê vé".

### 3. `modules/EC_Flight_Bookings/views/view.bksupplier.php` (tạo mới)
Class `Viewbksupplier extends SugarView`. Cấu trúc **mô phỏng `view.bkagent.php`** cho phần khung filter + render bảng, nhưng SQL lấy theo **mô hình debtopay**:

- `display()` + `populateContent($smarty)`: dựng bộ lọc ngày (`from_date`/`to_date`, mặc định hôm nay), `REPORT_TERM_LIST` (Hôm nay/Hôm qua/Tháng này/Tháng trước/Quý này/Quý trước) — copy nguyên logic [view.bkagent.php:24-82](../../modules/EC_Flight_Bookings/views/view.bkagent.php).
- Quyền truy cập: `is_admin` hoặc `isManagerUser` (đồng bộ với điều kiện menu).
- **Dropdown NCC:** dùng lại pattern picker Supplier có sẵn — `myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $selected, 'id', "AND account_type='Supplier' AND is_stop_tracking=0")` (đã dùng ở [LineDetails.php:19](../../modules/EC_Flight_Bookings/custom/viewedit/LineDetails.php) và [view.debtopay.php:396-403](../../modules/EC_Flight_Bookings/views/view.debtopay.php)). Giá trị chọn đọc từ `$_REQUEST['supplier_id']` (rỗng = tất cả NCC).

- **`populateSupplierSummary($from, $to)`** — bảng tổng hợp, mỗi dòng 1 NCC:
  ```sql
  SELECT d.supplier_id,
         a.ticker_symbol AS supplier_code,
         a.name          AS supplier_name,
         COUNT(DISTINCT d.booking_id)      AS sl_bk,
         SUM(IFNULL(d.quantity,0))         AS sl_ve,
         SUM(IFNULL(d.total_bought_price,0)) AS gia_mua,
         SUM(IFNULL(d.total_price,0))      AS gia_ban,
         SUM(IFNULL(d.supplier_discount,0)) AS chiet_khau,
         SUM(IFNULL(d.fee_bought,0))       AS phi_xuat_ve
  FROM ec_booking_details d
  LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
  LEFT JOIN accounts a ON a.id = d.supplier_id AND a.deleted = 0
  WHERE d.deleted = 0
    AND p.booking_status IN ('7','8')
    AND p.is_ticket_exported = 1
    AND d.supplier_id IS NOT NULL
    AND (IF(d.direction = 0, p.date_ticket_issue, p.date_ticket_inbound_issue)
         BETWEEN '$from' AND '$to')
  GROUP BY d.supplier_id
  ORDER BY gia_mua DESC
  ```
  Cột: STT | NCC (mã + tên, link `index.php?module=Accounts&action=DetailView&record=<supplier_id>`) | SL BK | SL vé | Chiết khấu | Phí xuất vé | Tổng giá bán | **Tổng giá mua**. Có dòng "Tổng" (str_replace placeholder `$TOTAL_*` như bkagent). Nếu `supplier_id` được chọn ở dropdown thì thêm `AND d.supplier_id = '<chọn>'`.

- **`populateSupplierAirlineDetail($from, $to)`** — bảng chi tiết tách **theo NCC × hãng** (JOIN `ec_booking_itineraries` lấy `airline_code`, chuẩn hoá VJ→VJA, VN→VNA giống bkagent), để khi bấm 1 NCC ở bảng trên thì JS lọc hiện các dòng hãng của NCC đó:
  - Mỗi dòng: `data-supplier="<supplier_id>"`, cột Hãng (dùng `myGetAirlineInfo2($code,'CODE')`), SL vé, Chiết khấu, Phí xuất vé, Giá bán, **Giá mua**.
  - Lưu ý double-count: SL vé nên tính từ `ec_booking_details` (đã có `direction`), không nhân bản qua join itineraries — cân nhắc gom SL vé theo `(supplier_id, airline_code, direction)` rồi cộng, hoặc lấy `airline_code` từ itinerary theo `direction` khớp với `d.direction` để tránh nhân đôi. **Đây là điểm rủi ro chính, cần test kỹ số liệu.**

  Cơ chế lọc client-side: copy pattern `data-airline` + click-to-filter của [view_bkagent.tpl:34-62](../../modules/EC_Flight_Bookings/tpls/view_bkagent.tpl) nhưng đổi thành `data-supplier`.

### 4. `modules/EC_Flight_Bookings/tpls/view_bksupplier.tpl` (tạo mới)
Copy khung từ [view_bkagent.tpl](../../modules/EC_Flight_Bookings/tpls/view_bkagent.tpl):
- Form `frmSearch` POST `index.php` với hidden `module=EC_Flight_Bookings`, `action=bksupplier`.
- Thêm `<select name="supplier_id" id="supplier_id">` (đổ từ `{$SUPPLIER_OPTIONS}`) cạnh bộ chọn ngày + nút "Xem".
- Bảng tổng hợp `{$SUPPLIER_SUMMARY_TBL}` (header: STT, NCC, SL BK, SL vé, Chiết khấu, Phí xuất vé, Tổng giá bán, Tổng giá mua).
- Bảng chi tiết `{$SUPPLIER_DETAIL_TBL}` (header: STT, NCC, Hãng, SL vé, Chiết khấu, Phí xuất vé, Giá bán, Giá mua).
- JS click-to-filter theo `data-supplier` + tính lại tổng đang hiển thị (port từ script bkagent).

## Điểm cần lưu ý / rủi ro

- **Ngày ghi nhận per-leg:** dùng `IF(direction=0, date_ticket_issue, date_ticket_inbound_issue)` trong cả WHERE lẫn khi hiển thị — đây chính là điểm khác biệt cốt lõi so với `bkagent` (đáp ứng yêu cầu #2, #3). Với booking 1 chiều, `date_ticket_inbound_issue` có thể NULL → dòng chặng về (nếu có) sẽ bị loại; cần kiểm tra dữ liệu 1 chiều vs khứ hồi.
- **Không dùng `calculateBKAmt()`** — hàm này ở cấp booking, không tách được theo NCC/leg. Toàn bộ tiền lấy trực tiếp từ `ec_booking_details`.
- **Hành lý ký gửi (luggage_purchase / luggage_purchase_inbound trên `ec_booking_passengers`):** debtopay có cộng phần này vào công nợ NCC. Quyết định phạm vi: **giai đoạn 1 chỉ tính `total_bought_price` của vé** cho gọn; nếu team cần khớp tuyệt đối với "Công nợ phải trả" thì bổ sung 2 nhánh UNION luggage như [view.debtopay.php:199-235](../../modules/EC_Flight_Bookings/views/view.debtopay.php) (theo `supplier_id`/`supplier_inbound_id`). → xác nhận lại với team sau khi xem bản đầu.
- **Chuẩn hoá mã hãng** VJ→VJA, VN→VNA để đồng nhất với `bkagent`.
- Giữ format tiền qua `format_number()`, ngày qua `$timedate->to_display_date()` như code hiện hữu.

## Kiểm thử (verification)

1. **Syntax:** `php -l` trên 2 file PHP mới + controller/Menu đã sửa (khớp CI: `find custom modules -name "*.php" | xargs -n1 php -l`).
2. **Truy cập UI:** đăng nhập tài khoản manager/admin → menu EC_Flight_Bookings phải thấy "Thống kê vé theo NCC" → mở `index.php?module=EC_Flight_Bookings&action=bksupplier`.
3. **Đối chiếu số liệu công nợ:** chọn 1 NCC + khoảng ngày, so **Tổng giá mua** với báo cáo "Công nợ phải trả" (`action=debtopay`) cùng NCC/kỳ — phần phát sinh vé (`total_bought_price`) phải khớp (bỏ qua chênh do luggage/voucher nếu giai đoạn 1 chưa tính).
4. **Kiểm tra ngày per-leg:** tạo/chọn 1 booking khứ hồi có `date_ticket_issue` và `date_ticket_inbound_issue` khác ngày (khác NCC càng tốt) → xác nhận mỗi chặng rơi vào đúng ngày lọc, không bị dồn 1 ngày.
5. **Không double-count SL vé:** chọn "tất cả NCC", tổng SL vé không vượt số vé thực của kỳ (so với `bkagent` cùng kỳ, chấp nhận lệch do khác tiêu chí ngày per-leg).
6. **Filter client-side:** bấm 1 dòng NCC ở bảng tổng hợp → bảng chi tiết chỉ hiện các hãng của NCC đó và tổng hiển thị cập nhật đúng.
