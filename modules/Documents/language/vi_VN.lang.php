<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$mod_strings = array(
    //module
    'LBL_MODULE_NAME' => 'Tài liệu',
    'LBL_MODULE_TITLE' => 'Tài liệu: Home',
    'LNK_NEW_DOCUMENT' => 'Tải lên tài liệu',
    'LNK_DOCUMENT_LIST' => 'D/s Tài liệu',
    'LBL_DOC_REV_HEADER' => 'Bản sửa Tài liệu',
    'LBL_SEARCH_FORM_TITLE' => 'Tìm kiếm Tài liệu',
    //vardef labels
    'LBL_NAME' => 'Tên Tài liệu',
    'LBL_DESCRIPTION' => 'Mô tả',
    'LBL_CATEGORY' => 'Danh mục',
    'LBL_SUBCATEGORY' => 'Danh mục phụ',
    'LBL_STATUS' => 'Tình trạng',
    'LBL_CREATED_BY' => 'Tạo bởi',
    'LBL_DATE_ENTERED' => 'Ngày tạo',
    'LBL_DATE_MODIFIED' => 'Ngày sửa',
    'LBL_DELETED' => 'Đã xóa',
    'LBL_MODIFIED' => 'Chỉnh sửa bởi Id',
    'LBL_MODIFIED_USER' => 'Sửa bởi',
    'LBL_CREATED' => 'Được ạo bởi',
    'LBL_REVISIONS' => 'Bản sửa',
    'LBL_RELATED_DOCUMENT_ID' => 'ID Tài liệu liên quan',
    'LBL_RELATED_DOCUMENT_REVISION_ID' => 'ID Bản sửa Tài liệu liên quan',
    'LBL_IS_TEMPLATE' => 'làm Mẫu',
    'LBL_TEMPLATE_TYPE' => 'Loại Tài liệu',
    'LBL_ASSIGNED_TO_NAME' => 'Giao cho:',
    'LBL_REVISION_NAME' => 'Số Bản sửa',
    'LBL_MIME' => 'Loại Mime',
    'LBL_REVISION' => 'Bản sửa',
    'LBL_DOCUMENT' => 'Tài liệu liên quan',
    'LBL_LATEST_REVISION' => 'Bản sửa cuối cùng',
    'LBL_CHANGE_LOG' => 'Log Thay đổi',
    'LBL_ACTIVE_DATE' => 'Ngày phát hành',
    'LBL_EXPIRATION_DATE' => 'Ngày hết hạn',
    'LBL_FILE_EXTENSION' => 'Tập tin mở rộng',
    'LBL_LAST_REV_MIME_TYPE' => 'Phiên bản cuối cùng kiểu MIME',
    'LBL_CAT_OR_SUBCAT_UNSPEC' => 'Không xác định',
    'LBL_HOMEPAGE_TITLE' => 'Tài liệu',
    //quick search
    'LBL_NEW_FORM_TITLE' => 'Tài liệu mới',
    //document edit and detail view
    'LBL_DOC_NAME' => 'Tên Tài liệu:',
    'LBL_FILENAME' => 'Tệp đính kèm:',
    'LBL_LIST_FILENAME' => 'Tập tin:',
    'LBL_DOC_VERSION' => 'Phiên bản:',
    'LBL_FILE_UPLOAD' => 'Tập tin:',

    'LBL_CATEGORY_VALUE' => 'Danh mục:',
    'LBL_LIST_CATEGORY' => 'Danh mục',
    'LBL_SUBCATEGORY_VALUE' => 'Danh mục phụ:',
    'LBL_DOC_STATUS' => 'Tình trạng:',
    'LBL_LAST_REV_CREATOR' => 'Bản sửa tạo bởi:',
    'LBL_LASTEST_REVISION_NAME' => 'Tên Bản sửa cuối:',
    'LBL_SELECTED_REVISION_NAME' => 'Tên Bản sửa được chọn:',
    'LBL_CONTRACT_STATUS' => 'Tình trạng Hợp đồng:',
    'LBL_CONTRACT_NAME' => 'Tên Hợp đồng:',
    'LBL_DET_RELATED_DOCUMENT' => 'Tài liệu liên quan:',
    'LBL_DET_RELATED_DOCUMENT_VERSION' => "Phiên bản Tài liệu liên quan:",
    'LBL_DET_IS_TEMPLATE' => 'Mẫu? :',
    'LBL_DET_TEMPLATE_TYPE' => 'Loại Tài liệu:',
    'LBL_DOC_DESCRIPTION' => 'Mô tả:',
    'LBL_DOC_ACTIVE_DATE' => 'Ngày phát hành:',
    'LBL_DOC_EXP_DATE' => 'Ngày hết hạn:',

    //document list view.
    'LBL_LIST_FORM_TITLE' => 'Danh sách Tài liệu',
    'LBL_LIST_DOCUMENT' => 'Tài liệu',
    'LBL_LIST_SUBCATEGORY' => 'Danh mục phụ',
    'LBL_LIST_REVISION' => 'Bản sửa',
    'LBL_LIST_LAST_REV_CREATOR' => 'phát hành bởi',
    'LBL_LIST_LAST_REV_DATE' => 'Ngày Bản sửa',
    'LBL_LIST_VIEW_DOCUMENT' => 'Xem',
    'LBL_LIST_ACTIVE_DATE' => 'Ngày phát hành',
    'LBL_LIST_EXP_DATE' => 'Ngày hết hạn',
    'LBL_LIST_STATUS' => 'Tình trạng',
    'LBL_LINKED_ID' => 'Linked id',
    'LBL_SELECTED_REVISION_ID' => 'ID Bản sửa được chọn',
    'LBL_LATEST_REVISION_ID' => 'ID Bản sửa cuối cùng',
    'LBL_SELECTED_REVISION_FILENAME' => 'Tên file Bản sửa đã chọn',
    'LBL_FILE_URL' => 'Tập tin url',

    //document search form.
    'LBL_SF_CATEGORY' => 'Danh mục:',
    'LBL_SF_SUBCATEGORY' => 'Danh mục phụ:',

    'DEF_CREATE_LOG' => 'Tài liệu đã được tạo',

    //error messages
    'ERR_DOC_NAME' => 'Tên Tài liệu',
    'ERR_DOC_ACTIVE_DATE' => 'Ngày phát hành',
    'ERR_FILENAME' => 'Tệp đính kèm',
    'ERR_DOC_VERSION' => 'Phiên bản Tài liệu',
    'ERR_DELETE_CONFIRM' => 'Bản có muốn xóa Tài liệu này?',
    'ERR_DELETE_LATEST_VERSION' => 'Bạn không thể xóa được Tài liệu này.',
    'LNK_NEW_MAIL_MERGE' => 'Mail Merge',
    'ERR_MISSING_FILE' => 'Tài liệu này là thiếu một tập tin, có nhiều khả năng do một lỗi trong khi tải lên. Xin vui lòng thử lại tải lên các tập tin hoặc liên hệ với người quản trị.',

    //sub-panel vardefs.
    'LBL_LIST_DOCUMENT_NAME' => 'Tên',
    'LBL_LIST_IS_TEMPLATE' => 'Mẫu?',
    'LBL_LIST_TEMPLATE_TYPE' => 'Loại Tài liệu',
    'LBL_LAST_REV_CREATE_DATE' => 'Ngày tạo bản sửa cuối',
    'LBL_CONTRACTS' => 'Hợp đồng',
    'LBL_CREATED_USER' => 'Người tạo',
    'LBL_DOCUMENT_INFORMATION' => 'Xem toàn bộ Tài liệu', //No need to be translated in all caps. Translation used just in menu action items when using the SuiteP template
    'LBL_DOC_ID' => 'ID Nguồn Tài liệu',
    'LBL_DOC_TYPE' => 'Nguồn',
    'LBL_DOC_TYPE_POPUP' => 'Chọn một nguồn mà tài liệu này sẽ là tải lên <br>và từ đó nó sẽ có sẵn.',
    'LBL_DOC_URL' => 'Nguồn Tài liệu URL',
    'LBL_SEARCH_EXTERNAL_DOCUMENT' => 'Tệp đính kèm',
    'LBL_EXTERNAL_DOCUMENT_NOTE' => '20 lần đầu tiên đặt mới sửa đổi các tập tin được hiển thị theo thứ tự trong danh sách dưới đây giảm. Sử dụng tìm kiếm để tìm các tập tin khác.',
    'LBL_LIST_EXT_DOCUMENT_NAME' => 'Tệp đính kèm',
    'ERR_INVALID_EXTERNAL_API_ACCESS' => 'Người dùng cố gắng truy cập một không hợp lệ ngoài API ({0})',
    'ERR_INVALID_EXTERNAL_API_LOGIN' => 'Kiểm tra đăng nhập thất bại cho bên ngoài API ({0})',

    // Links around the world
    'LBL_ACCOUNTS_SUBPANEL_TITLE' => 'Tài khoản',
    'LBL_CONTACTS_SUBPANEL_TITLE' => 'Liên hệ',
    'LBL_OPPORTUNITIES_SUBPANEL_TITLE' => 'Cơ hội',
    'LBL_CASES_SUBPANEL_TITLE' => 'Vụ việc',
    'LBL_BUGS_SUBPANEL_TITLE' => 'Lỗi',

    'LBL_AOS_CONTRACTS' => 'Hợp đồng',


    'LBL_BOOKING_NAME' => 'Đặt chỗ',
    'LBL_PREVIEW_PHOTO' => 'Xem trước',


);
