# Phân biệt module "Người dùng" (Users) và "Nhân viên" (Employees)

> Tài liệu dành cho admin mới bắt đầu sử dụng CRM. Mục tiêu: tránh nhầm lẫn giữa hai module trông có vẻ trùng nhau, dẫn đến thao tác sai (ví dụ: cố tạo nhân viên mới từ module Nhân viên).

## 1. Sự thật kỹ thuật quan trọng nhất

**Users và Employees không phải hai kho dữ liệu khác nhau — chúng đọc/ghi vào CÙNG MỘT bảng `users` trong database.**

Trong code, bean `Employee` khai báo thẳng:

```php
public $table_name = "users";
```

Nói cách khác: **mỗi "Nhân viên" chính là một "Người dùng"**, chỉ được xem qua hai màn hình (module) khác nhau, mỗi màn hình hiển thị một nhóm trường thông tin khác nhau của cùng một bản ghi. Sửa một trường dùng chung ở module này (ví dụ chức danh, phòng ban, số điện thoại) sẽ lập tức phản ánh sang module kia, vì thực chất chỉ có một dòng dữ liệu duy nhất.

## 2. Vì sao không tạo được nhân viên mới trong module Nhân viên

- Employees quản lý phần **thông tin nhân sự/lương** của một tài khoản đã tồn tại.
- Mọi nhân sự bắt buộc phải có tài khoản đăng nhập (`user_name`, mật khẩu, phân quyền) — mà việc này chỉ có thể tạo từ module **Users**.

Nếu cần thêm người mới, **luôn luôn tạo từ Users**, không tìm cách tạo từ Employees.

## 3. Khi nào dùng module nào

### Users ("Người dùng") — dùng để:

- **Tạo tài khoản mới** cho nhân sự (bắt buộc thực hiện ở đây).
- Đặt/đổi tên đăng nhập, mật khẩu.
- Phân loại người dùng: Người dùng thường / Quản trị / Quản trị người dùng / Nhóm / Portal.
- Gán vai trò, nhóm bảo mật (Security Groups).
- Cấu hình máy nhánh (SIP), mật khẩu máy nhánh, ký hiệu đại lý.
- Bật xác thực 2 lớp (2FA).
- Đặt trạng thái tài khoản: Đang hoạt động / Ngừng hoạt động.
- Thông tin cơ bản cũng có ở đây (họ tên, chức danh, phòng ban, số điện thoại...) qua panel "Thông tin nhân viên" — nhưng đây là bản rút gọn, không đầy đủ như bên Employees.

### Employees ("Nhân viên") — dùng để:

- **Cập nhật** (không tạo mới) thông tin nhân sự của một tài khoản đã có sẵn.
- Nhập lương cơ bản và các khoản phụ cấp: xăng xe, ăn trưa, điện thoại, trách nhiệm, thâm niên, phụ cấp khác 1/2.
- Ngày phép còn lại, ngày bắt đầu làm việc.
- Xem lịch sử công tác (Work History).
- Một số trường (lương, phụ cấp) **chỉ Quản lý (IS_MANAGER) mới sửa được** — nhân viên thường hoặc admin không phải quản lý trực tiếp sẽ chỉ xem được giá trị, không sửa được.

Employees **không có** các trường đăng nhập/bảo mật (không có user_name, mật khẩu, loại người dùng, 2FA, SIP) — những trường đó chỉ tồn tại và chỉ sửa được ở Users.

## 4. Quy trình chuẩn khi thêm nhân sự mới

1. Vào **Users** → "Tạo người dùng" → nhập tên đăng nhập, mật khẩu, loại người dùng, thông tin cơ bản (họ tên, phòng ban, chức danh...) → Lưu.
2. Vào **Employees** → tìm đúng người vừa tạo trong "D/s nhân viên" → mở để bổ sung lương, các khoản phụ cấp, ngày phép nếu cần.

Không có bước "tạo nhân viên" riêng — bước 1 đã tự động tạo luôn cả "nhân viên" đó, vì là cùng một bản ghi.

## 5. Bảng tóm tắt nhanh

| Việc cần làm | Làm ở module nào |
| --- | --- |
| Thêm người mới vào hệ thống | **Users** (bắt buộc) |
| Đổi mật khẩu / tên đăng nhập | Users |
| Phân quyền, vai trò, nhóm bảo mật | Users |
| Cấu hình máy nhánh SIP | Users |
| Khoá / mở tài khoản | Users |
| Nhập lương, phụ cấp | Employees |
| Ngày phép, ngày vào làm | Employees |
| Xem lịch sử công tác | Employees |
| Sửa chức danh / phòng ban / SĐT | Cả hai (cùng dữ liệu) |
