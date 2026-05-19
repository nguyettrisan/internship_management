<?php

// =========================
// Module / Menu
// =========================
$lang['internship'] = 'Quản lý Internship';
$lang['internship_applications'] = 'Ứng viên';
$lang['internship_job_orders'] = 'Đơn tuyển';

// =========================
// Application fields
// =========================
$lang['application_full_name'] = 'Họ và tên';
$lang['application_gender'] = 'Giới tính';
$lang['application_school'] = 'Trường';
$lang['application_major'] = 'Ngành';
$lang['application_phone_student'] = 'Số điện thoại';
$lang['application_phone_parent'] = 'Số ĐT phụ huynh';
$lang['application_address'] = 'Địa chỉ';
$lang['application_japanese_level'] = 'Trình độ tiếng Nhật';
$lang['application_created_date'] = 'Ngày ứng tuyển';

$lang['interview_result'] = 'Kết quả phỏng vấn';
$lang['interview_pass'] = 'Đạt';
$lang['interview_fail'] = 'Rớt';
$lang['interview_not_yet'] = 'Chưa đánh giá';

// =========================
// Job order fields
// =========================
$lang['job_order'] = 'Đơn tuyển';
$lang['job_order_company'] = 'Công ty';
$lang['job_order_title'] = 'Vị trí tuyển dụng';
$lang['job_order_major'] = 'Ngành nghề';
$lang['job_order_date'] = 'Ngày tạo';

// =========================
// AI / Upload
// =========================
$lang['ai_extract'] = 'Trích xuất AI';
$lang['ai_extract_success'] = 'Đã trích xuất thông tin thành công';
$lang['ai_extract_error'] = 'Không thể trích xuất dữ liệu';

$lang['upload_cv'] = 'Tải lên CV';
$lang['upload_avatar'] = 'Ảnh đại diện';

// =========================
// Buttons
// =========================
$lang['btn_save'] = 'Lưu';
$lang['btn_delete'] = 'Xoá';
$lang['btn_edit'] = 'Chỉnh sửa';
$lang['btn_add_new'] = 'Thêm mới';

// ===================================================
// STATUS PIPELINE (ĐÚNG theo dropdown anh chụp)
// Key dùng trong view: _l('intern_status_' . $status)
// ===================================================
$lang['intern_status_applied']            = 'Ứng tuyển';
$lang['intern_status_interview']          = 'Hẹn phỏng vấn';
$lang['intern_status_prepare_documents']  = 'Chuẩn bị hồ sơ';
$lang['intern_status_complete_documents'] = 'Hoàn thành hồ sơ';
$lang['intern_status_waiting_coe']        = 'Đợi COE';
$lang['intern_status_has_coe']            = 'Đã có COE';
$lang['intern_status_visa']               = 'Làm visa';
$lang['intern_status_buy_ticket']         = 'Mua vé nhập cảnh';
$lang['intern_status_prepare_flight']     = 'Chuẩn bị bay';
$lang['intern_status_in_japan']           = 'Đang ở Nhật';
$lang['intern_status_returned']           = 'Đã về nước';
$lang['intern_status_cancelled']          = 'Huỷ';
$lang['intern_status_not_updated']        = 'Chưa cập nhật';

// ===================================================
// ALIAS (phòng trường hợp DB lưu key khác)
// ===================================================
// Ứng tuyển
$lang['intern_status_apply']              = 'Ứng tuyển';
$lang['intern_status_applying']           = 'Ứng tuyển';

// Hẹn phỏng vấn
$lang['intern_status_interviewing']       = 'Hẹn phỏng vấn';
$lang['intern_status_schedule_interview'] = 'Hẹn phỏng vấn';

// Chuẩn bị hồ sơ
$lang['intern_status_docs_preparing']     = 'Chuẩn bị hồ sơ';
$lang['intern_status_preparing_docs']     = 'Chuẩn bị hồ sơ';
$lang['intern_status_docs_prepare']       = 'Chuẩn bị hồ sơ';

// Hoàn thành hồ sơ
$lang['intern_status_docs_completed']     = 'Hoàn thành hồ sơ';
$lang['intern_status_completed_docs']     = 'Hoàn thành hồ sơ';
$lang['intern_status_documents_completed']= 'Hoàn thành hồ sơ';

// Đợi COE
$lang['intern_status_wait_coe']           = 'Đợi COE';
$lang['intern_status_coe_waiting']        = 'Đợi COE';

// Đã có COE
$lang['intern_status_coe_received']       = 'Đã có COE';
$lang['intern_status_received_coe']       = 'Đã có COE';

// Làm visa
$lang['intern_status_visa_processing']    = 'Làm visa';
$lang['intern_status_making_visa']        = 'Làm visa';

// Mua vé nhập cảnh
$lang['intern_status_buying_ticket']      = 'Mua vé nhập cảnh';
$lang['intern_status_entry_ticket']       = 'Mua vé nhập cảnh';

// Chuẩn bị bay
$lang['intern_status_preparing_flight']   = 'Chuẩn bị bay';
$lang['intern_status_flight_preparing']   = 'Chuẩn bị bay';

// Đang ở Nhật
$lang['intern_status_working_in_japan']   = 'Đang ở Nhật';

// Đã về nước
$lang['intern_status_back_home']          = 'Đã về nước';

// Huỷ
$lang['intern_status_cancel']             = 'Huỷ';

// Chưa cập nhật
$lang['intern_status_none']               = 'Chưa cập nhật';
$lang['intern_status_unupdated']          = 'Chưa cập nhật';

// ===================================================
// (GIỮ LẠI các key status_* hiện hữu nếu hệ thống dùng chỗ khác)
// ===================================================
$lang['status_prepare_documents'] = 'Chuẩn bị hồ sơ';
$lang['status_interview_fail']    = 'Rớt phỏng vấn';
$lang['status_interview_pass']    = 'Đạt phỏng vấn';
$lang['status_received']          = 'Tiếp nhận đơn';