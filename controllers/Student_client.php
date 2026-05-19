<?php defined('BASEPATH') or exit('No direct script access allowed');

class Student_client extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Model cũ
        $this->load->model('internship_management/Student_client_model', 'model');

        // Model hóa đơn (local module)
        $this->load->model('internship_management/Internship_invoices_model', 'inv');

        // Model ghi chú nội bộ
        $this->load->model('internship_management/Internship_notes_model', 'notes');

        // Core CRM models (Perfex)
        $this->load->model('clients_model');

        $this->load->helper(['url', 'form']);
        $this->load->model('internship_management/Im_audit_log_model', 'im_audit');
 $this->load->helper('internship_management/im_audit');
 if ($this->input->method() === 'post') {

    $post = $this->input->post(NULL, true);

    // lọc dữ liệu nhạy cảm
    foreach (['password','pass','smtp_pass','api_key','token'] as $k) {
        if (isset($post[$k])) $post[$k] = '***';
    }

    im_audit_log(
        'http',
        0,
        'http_post',
        'POST vào Internship Applications',
        null,
        [
            'url' => current_url(),
            'staff_id' => get_staff_user_id(),
            'post' => $post
        ]
    );
}
    }

    /* ============================================================================
        1. DASHBOARD HỒ SƠ SINH VIÊN
    ============================================================================ */
    public function view($student_id = null)
    {
        if (!$student_id) {
            show_404();
        }

        // ID truyền từ danh sách thường là application_id, không phải student_id.
        // Resolve để tránh 404.
        $ctx = $this->model->resolve_student_context($student_id);
        $student = $ctx['student'] ?? null;
        if (empty($student)) {
            show_404();
        }

        $crm_client_id = (int)($ctx['crm_client_id'] ?? 0);
        if ($crm_client_id <= 0) {
            foreach (['crm_id','client_id','userid','crm_client_id','crm_customer_id'] as $k) {
                if (!empty($student[$k]) && (int)$student[$k] > 0) { $crm_client_id = (int)$student[$k]; break; }
            }
        }

        // Build avatar URL for view (avoid broken image)
        $avatar_url = $this->_resolve_student_avatar_url($student);
        $student['avatar_url'] = $avatar_url;

        // Resolve job/order info for view (view.php expects $job)
        $job = null;
        $job_id = 0;
        foreach (['job_order_id','job_id','internship_job_order_id','internship_job_id'] as $k) {
            if (isset($student[$k]) && (int)$student[$k] > 0) { $job_id = (int)$student[$k]; break; }
        }
        $job_table = null;
        foreach ([db_prefix().'internship_job_orders', db_prefix().'tblinternship_job_orders', db_prefix().'internship_jobs'] as $t) {
            if ($this->db->table_exists($t)) { $job_table = $t; break; }
        }
        if ($job_table && $job_id > 0) {
            $job = $this->db->get_where($job_table, ['id' => $job_id])->row();
        }
        // Local data chỉ load khi có student_id thật (tránh query sai)
        $localStudentId = (int)($ctx['student_id'] ?? 0);
        // Applications list (student may apply multiple jobs)
        $applications = [];
        $realStudentId = (int)($ctx['student_id'] ?? 0);
        if ($realStudentId > 0) {
            $tblApp = db_prefix().'internship_applications';
            if ($this->db->table_exists($tblApp)) {
                $applications = $this->db->where('student_id', $realStudentId)->order_by('id','DESC')->get($tblApp)->result_array();
            }
        }

        $data = [
            'context_id'      => (int)$student_id,
            'student_id'      => $localStudentId > 0 ? $localStudentId : (int)$student_id,
            'application_id'  => (int)($ctx['application_id'] ?? 0),
            'student'         => $student,
            'applications'    => $applications,
            'avatar_url'      => $avatar_url,
            'job'             => $job,
            'job_order'       => $job,
            'crm_client_id'   => $crm_client_id,
            'crm_invoices'    => $crm_client_id > 0 ? $this->model->get_crm_invoices($crm_client_id) : [],
            'crm_contracts'   => $crm_client_id > 0 ? $this->model->get_crm_contracts($crm_client_id) : [],
            'contracts'       => $localStudentId > 0 ? $this->model->get_contracts($localStudentId) : [],
            'invoices'        => $localStudentId > 0 ? $this->model->get_invoices($localStudentId) : [],
            'files'           => $localStudentId > 0 ? $this->_get_files_for_student($localStudentId) : [],
            'logs'            => $localStudentId > 0 ? $this->_get_logs_for_student($localStudentId) : [],
            'notes'           => $localStudentId > 0 ? $this->_get_notes_for_student($localStudentId) : [],
            'title'           => 'Hồ sơ sinh viên: ' . ($student['full_name'] ?? ('#' . (int)$student_id)),
        ];

        // Activity log: view profile (only when we have a real local student_id)
        if ($localStudentId > 0) {
            $this->model->add_log($localStudentId, 'Xem hồ sơ sinh viên', 'view_profile');
        }
        // CRM link info for view
        $data['client_id'] = $crm_client_id;
        $data['crm_link'] = [
            'linked' => $crm_client_id > 0,
            'crm_id' => $crm_client_id,
            'crm_url' => $crm_client_id > 0 ? admin_url('clients/client/'.$crm_client_id) : '',
        ];
$this->load->view('internship_management/student_client/view', $data);
    }

    /* ==========================================================================
        1B. ĐẨY CRM – TẠO/LINK CUSTOMER
        - Nếu email đã tồn tại contact → link vào customer có sẵn
        - Nếu đã có crm_client_id → update thông tin
        - Nếu chưa có → tạo mới customer + contact primary
    ========================================================================= */
    public function push_crm_client($student_id = null)
    {
        if (!$student_id) {
            show_404();
        }

        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        $ctx = $this->model->resolve_student_context($student_id);
        $student = $ctx['student'] ?? null;
        if (empty($student)) {
            show_404();
        }

        $crm_client_id = (int)($ctx['crm_client_id'] ?? 0);
        if ($crm_client_id <= 0) {
            foreach (['crm_id','client_id','userid','crm_client_id','crm_customer_id'] as $k) {
                if (!empty($student[$k]) && (int)$student[$k] > 0) { $crm_client_id = (int)$student[$k]; break; }
            }
        }

        $localStudentId = (int)($ctx['student_id'] ?? 0);

        // Chỉ dùng các field tối thiểu theo yêu cầu: tên, SĐT, địa chỉ, email
        $fullName = trim((string)($student['full_name'] ?? ''));
        if ($fullName === '') {
            $fullName = 'Student #' . (int)$student_id;
        }
        $phone   = (string)($student['phone_student'] ?? ($student['phone'] ?? ''));
        $address = (string)($student['address'] ?? '');
        $email   = (string)($student['email'] ?? '');

        // 1) Link theo email nếu tồn tại contact → link vào customer có sẵn
        $tblContacts = db_prefix() . 'contacts';
        if ($email !== '' && $this->db->table_exists($tblContacts)) {
            $existingContact = $this->db->where('email', $email)->get($tblContacts)->row_array();
            if (!empty($existingContact['userid'])) {
                $clientId = (int)$existingContact['userid'];
                $this->model->set_crm_client($student_id, $clientId, 'synced', null);
                if ($localStudentId > 0) {
                    $this->model->add_log($localStudentId, 'Liên kết CRM theo email (trùng contact): Customer #' . $clientId, 'link_crm');
                }
                set_alert('success', 'Đã liên kết vào Customer CRM có sẵn (trùng email).');
                redirect(admin_url('clients/client/' . $clientId));
            }
        }

        // 2) Nếu đã có client → update (chỉ các field tối thiểu)
        if ($crm_client_id > 0) {
            $update = [
                'company'     => $fullName,
                'phonenumber' => $phone,
                'address'     => $address,
            ];
            // hạn chế warning deprecated hook làm trắng trang (server bật display_errors)
            $oldLevel = error_reporting();
            error_reporting($oldLevel & ~E_USER_NOTICE);
            $ok = $this->clients_model->update($update, $crm_client_id);
            error_reporting($oldLevel);

            $this->model->mark_crm_sync($student_id, $ok ? 'synced' : 'error', $ok ? null : 'CRM: update client failed');
            if ($ok && $localStudentId > 0) {
                $this->model->add_log($localStudentId, 'Đồng bộ thông tin CRM: Customer #' . $crm_client_id, 'push_crm');
            }
            set_alert($ok ? 'success' : 'danger', $ok ? 'Đã đồng bộ Customer CRM.' : 'Không thể đồng bộ Customer CRM.');
            redirect(admin_url('internship_management/student_client/view/' . $student_id));
        }

        // 3) Tạo mới
        $clientData = [
            'company'     => $fullName,
            'phonenumber' => $phone,
            'address'     => $address,
        ];

        // Perfex 2.9.4+ có deprecated hook after_client_added → tránh hiển thị notice
        $oldLevel = error_reporting();
        error_reporting($oldLevel & ~E_USER_NOTICE);
        $clientId = (int)$this->clients_model->add($clientData);
        error_reporting($oldLevel);
        if ($clientId > 0) {
            // Tạo contact primary (không dùng contacts_model để tránh lỗi "Unable to locate model")
            if ($email !== '' && $this->db->table_exists($tblContacts)) {
                $exists = $this->db->where('email', $email)->get($tblContacts)->row_array();
                if (!$exists) {
                    $insert = [
                        'userid'    => $clientId,
                        'firstname' => $fullName,
                        'lastname'  => '',
                        'email'     => $email,
                        'phonenumber' => $phone,
                        'is_primary' => 1,
                        'active'     => 1,
                    ];
                    // set các field nếu tồn tại trong schema
                    if ($this->db->field_exists('datecreated', $tblContacts)) {
                        $insert['datecreated'] = date('Y-m-d H:i:s');
                    }
                    $this->db->insert($tblContacts, $insert);
                }
            }

            $this->model->set_crm_client($student_id, $clientId, 'synced', null);
            if ($localStudentId > 0) {
                $this->model->add_log($localStudentId, 'Tạo mới khách hàng CRM: Customer #' . $clientId, 'create_crm');
            }
            set_alert('success', 'Đã tạo Customer CRM và liên kết vào sinh viên.');
            redirect(admin_url('clients/client/' . $clientId));
        }

        $this->model->mark_crm_sync($student_id, 'error', 'CRM: create client failed');
        set_alert('danger', 'Không thể tạo Customer CRM.');
        redirect(admin_url('internship_management/student_client/view/' . $student_id));
    }

    /* ==========================================================================
        1C. ĐẨY CRM – TẠO HÓA ĐƠN CRM (DRAFT)
    ========================================================================= */
    public function push_crm_invoice($invoice_id = null)
    {
        if (!$invoice_id) show_404();

        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        $invoice = $this->model->get_invoice($invoice_id);
        if (!$invoice) show_404();

        $student = $this->model->get_student($invoice['student_id']);
        if (!$student) show_404();

        if (empty($student['crm_client_id'])) {
            set_alert('warning', 'Sinh viên chưa liên kết CRM. Hãy bấm “Đẩy CRM” trước.');
            redirect(admin_url('internship_management/student_client/invoice_view/' . $invoice_id));
        }

        $items = $this->model->get_invoice_items($invoice_id);

        $this->load->model('invoices_model');

        $newitems = [];
        $order = 1;
        foreach ($items as $it) {
            $newitems[] = [
                'description'      => $it['item_name'] ?? '',
                'long_description' => $it['description'] ?? '',
                'qty'              => (float)($it['qty'] ?? 1),
                'unit'             => $it['unit'] ?? '',
                'rate'             => (float)($it['rate'] ?? 0),
                'order'            => $order++,
            ];
        }

        $data = [
            'clientid'  => (int)$student['crm_client_id'],
            'date'      => $invoice['invoice_date'] ?? date('Y-m-d'),
            'duedate'   => $invoice['due_date'] ?? null,
            'status'    => 1, // Draft
            'newitems'  => $newitems,
            'adminnote' => 'Đẩy từ module Internship. Local invoice #' . (int)$invoice_id,
        ];

        $crmInvoiceId = 0;
        $err = null;
        try {
            $crmInvoiceId = (int)$this->invoices_model->add($data);
        } catch (Exception $e) {
            $err = $e->getMessage();
        }

        if ($crmInvoiceId > 0) {
            // lưu mapping nếu DB có cột
            $tbl = $this->db->table_exists(db_prefix() . 'internship_invoices') ? db_prefix() . 'internship_invoices' : ( $this->db->table_exists('tblinternship_invoices') ? 'tblinternship_invoices' : null );
            if ($tbl) {
                $update = [];
                if ($this->db->field_exists('crm_invoice_id', $tbl)) {
                    $update['crm_invoice_id'] = $crmInvoiceId;
                }
                if ($this->db->field_exists('crm_sync_status', $tbl)) {
                    $update['crm_sync_status'] = 'synced';
                }
                if ($this->db->field_exists('crm_last_synced_at', $tbl)) {
                    $update['crm_last_synced_at'] = date('Y-m-d H:i:s');
                }
                if ($this->db->field_exists('crm_last_error', $tbl)) {
                    $update['crm_last_error'] = null;
                }
                if (!empty($update)) {
                    $this->db->where('id', (int)$invoice_id)->update($tbl, $update);
                }
            }

            set_alert('success', 'Đã tạo hóa đơn CRM (Draft).');
            redirect(admin_url('invoices/list_invoices/' . $crmInvoiceId));
        }

        set_alert('danger', !empty($err) ? $err : 'Không thể tạo hóa đơn CRM.');
        redirect(admin_url('internship_management/student_client/invoice_view/' . $invoice_id));
    }

    /* ==========================================================================
        1D. ĐẨY CRM – TẠO HỢP ĐỒNG CRM
    ========================================================================= */
    public function push_crm_contract($contract_id = null)
    {
        if (!$contract_id) show_404();

        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        $contract = $this->model->get_contract($contract_id);
        if (!$contract) show_404();

        $student = $this->model->get_student($contract['student_id']);
        if (!$student) show_404();

        if (empty($student['crm_client_id'])) {
            set_alert('warning', 'Sinh viên chưa liên kết CRM. Hãy bấm “Đẩy CRM” trước.');
            redirect(admin_url('internship_management/student_client/contract_view/' . $contract_id));
        }

        $this->load->model('contracts_model');

        $data = [
            'client'         => (int)$student['crm_client_id'],
            'datestart'      => date('Y-m-d'),
            'dateend'        => null,
            'subject'        => $contract['contract_name'] ?? ('Hợp đồng #' . (int)$contract_id),
            'description'    => $contract['content'] ?? '',
            'content'        => $contract['content'] ?? '',
            'contract_value' => 0,
        ];

        $crmContractId = 0;
        $err = null;
        try {
            $crmContractId = (int)$this->contracts_model->add($data);
        } catch (Exception $e) {
            $err = $e->getMessage();
        }

        if ($crmContractId > 0) {
            // lưu mapping nếu DB có cột
            $tbl = $this->db->table_exists(db_prefix() . 'internship_contracts') ? db_prefix() . 'internship_contracts' : ( $this->db->table_exists('tblinternship_contracts') ? 'tblinternship_contracts' : null );
            if ($tbl) {
                $update = [];
                if ($this->db->field_exists('crm_contract_id', $tbl)) {
                    $update['crm_contract_id'] = $crmContractId;
                }
                if ($this->db->field_exists('crm_sync_status', $tbl)) {
                    $update['crm_sync_status'] = 'synced';
                }
                if ($this->db->field_exists('crm_last_synced_at', $tbl)) {
                    $update['crm_last_synced_at'] = date('Y-m-d H:i:s');
                }
                if ($this->db->field_exists('crm_last_error', $tbl)) {
                    $update['crm_last_error'] = null;
                }
                if (!empty($update)) {
                    $this->db->where('id', (int)$contract_id)->update($tbl, $update);
                }
            }

            set_alert('success', 'Đã tạo hợp đồng CRM.');
            redirect(admin_url('contracts/contract/' . $crmContractId));
        }

        set_alert('danger', !empty($err) ? $err : 'Không thể tạo hợp đồng CRM.');
        redirect(admin_url('internship_management/student_client/contract_view/' . $contract_id));
    }

    /* ============================================================================
        2. DANH SÁCH HÓA ĐƠN THEO SINH VIÊN
    ============================================================================ */
    public function invoices($student_id = null)
    {
        if (!$student_id) show_404();
        $student = $this->model->get_student($student_id);
        if (!$student) show_404();

        $data = [
            'student_id' => $student_id,
            'student'    => $student,
            'invoices'   => $this->model->get_invoices($student_id),
            'title'      => 'Hóa đơn – ' . ($student['full_name'] ?? ''),
        ];

        $this->load->view('internship_management/student_client/invoices', $data);
    }

    /* ============================================================================
        3. TẠO HÓA ĐƠN
        - Lưu invoice + lưu invoice_items
    ============================================================================ */
    public function invoice_create($student_id = null)
    {
        if (!$student_id) show_404();
        $student = $this->model->get_student($student_id);
        if (!$student) show_404();

        if ($this->input->post()) {

            $post = $this->input->post();

            /* ===============================
               CHUYỂN DỮ LIỆU ITEMS ĐÚNG DẠNG
            ================================*/

            $items = [];
            if (!empty($post['items']['name'])) {

                foreach ($post['items']['name'] as $i => $itemName) {

                    $itemName = trim($itemName);
                    if ($itemName === '') continue;

                    $qty      = (float)($post['items']['qty'][$i] ?? 1);
                    $rate     = (float)($post['items']['price'][$i] ?? 0);
                    $tax_rate = (float)($post['items']['tax_rate'][$i] ?? 0);

                    $items[] = [
                        'item_name'   => $itemName,
                        'description' => $post['items']['desc'][$i] ?? '',
                        'unit'        => $post['items']['unit'][$i] ?? '',
                        'qty'         => $qty,
                        'rate'        => $rate,
                        'tax_rate'    => $tax_rate,
                    ];
                }
            }

            /* ===================================
               TẠO HÓA ĐƠN ĐÚNG MODEL
            ====================================*/

            $inv = [
                'invoice_code' => $post['invoice_code'] ?? null,
                'invoice_date' => $post['invoice_date'] ?? null,
                'due_date'     => $post['due_date'] ?? null,
                'status'       => $post['status'] ?? 'unpaid',
                'description'  => $post['description'] ?? '',
                'content'      => $post['content'] ?? '',
            ];

            $invoice_id = $this->inv->create($student_id, $inv, $items);

            if ($invoice_id) {
                set_alert('success', 'Đã tạo hóa đơn mới.');
                redirect(admin_url('internship_management/student_client/invoice_view/' . $invoice_id));
            }

            set_alert('danger', 'Không thể tạo hóa đơn!');
        }

        $data = [
            'student'    => $student,
            'student_id' => $student_id,
            'title'      => 'Tạo hóa đơn – ' . ($student['full_name'] ?? ''),
        ];

        $this->load->view('internship_management/student_client/invoice_create', $data);
    }

    /* ============================================================================
        4. XEM HÓA ĐƠN + ITEMS
    ============================================================================ */
    public function invoice_view($invoice_id = null)
    {
        if (!$invoice_id) show_404();

        $invoice = $this->model->get_invoice($invoice_id);
        if (!$invoice) show_404();

        $student    = $this->model->get_student($invoice['student_id']);
        $items      = $this->model->get_invoice_items($invoice_id);
        $signatures = $this->model->get_signatures('invoice', $invoice_id);

        $data = [
            'invoice'    => $invoice,
            'student'    => $student,
            'student_id' => $invoice['student_id'],
            'items'      => $items,
            'signatures' => $signatures,
            'title'      => 'Hóa đơn #' . ($invoice['invoice_code'] ?? (int)$invoice_id),
        ];

        $this->load->view('internship_management/student_client/invoice_view', $data);
    }

    /* ============================================================================
        4B. CHỈNH SỬA HÓA ĐƠN
        - dùng Internship_invoices_model thông qua wrapper update_invoice_full
    ============================================================================ */
    public function invoice_edit($invoice_id = null)
    {
        if (!$invoice_id) show_404();

        $invoice = $this->model->get_invoice($invoice_id);
        if (!$invoice) show_404();

        $student = $this->model->get_student($invoice['student_id']);
        if (!$student) show_404();

        if ($this->input->post()) {

            $post = $this->input->post();

            // Tổng tiền gửi từ form (đã tính sẵn ở JS edit)
            $rawAmount = $post['amount'] ?? 0;
            if (is_string($rawAmount)) {
                $rawAmount = str_replace(['.', ',', ' '], ['', '', ''], $rawAmount);
            }
            $amount = (float)$rawAmount;

            // Chuẩn bị dữ liệu update cho bảng hóa đơn (old fields)
            $update_data = [
                'invoice_code' => trim($post['invoice_code'] ?? ($invoice['invoice_code'] ?? '')),
                'total'        => $amount,
                'status'       => $post['status'] ?? ($invoice['status'] ?? 'unpaid'),
                'due_date'     => !empty($post['due_date']) ? $post['due_date'] : null,
                'description'  => $post['description'] ?? '',
                'invoice_date' => !empty($post['date']) ? $post['date'] : ($invoice['invoice_date'] ?? date('Y-m-d')),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

            // Xử lý ITEMS (format items[index][field])
            $items_data = [];
            if (!empty($post['items']) && is_array($post['items'])) {

                foreach ($post['items'] as $row) {

                    $name = trim($row['item_name'] ?? '');
                    if ($name === '') continue;

                    // qty
                    $qty = isset($row['qty']) && $row['qty'] !== ''
                        ? (float)$row['qty']
                        : 0;

                    // rate
                    $rawRate = $row['rate'] ?? 0;
                    if (is_string($rawRate)) {
                        $rawRate = str_replace(['.', ',', ' '], ['', '', ''], $rawRate);
                    }
                    $rate = (float)$rawRate;

                    // tax %
                    $tax_rate = isset($row['tax_rate']) && $row['tax_rate'] !== ''
                        ? (float)$row['tax_rate']
                        : 0;

                    $line      = $qty * $rate;
                    $taxAmount = $line * $tax_rate / 100;
                    $lineTotal = $line + $taxAmount;

                    $items_data[] = [
                        'item_name'   => $name,
                        'description' => $row['description'] ?? '',
                        'unit'        => $row['unit'] ?? '',
                        'qty'         => $qty,
                        'rate'        => $rate,
                        'tax_rate'    => $tax_rate,
                        'amount'      => $lineTotal,
                    ];
                }
            }

            // Update invoice + items
            $this->model->update_invoice_full($invoice_id, $update_data, $items_data);

            set_alert('success', 'Đã cập nhật hóa đơn thành công.');
            redirect(admin_url('internship_management/student_client/invoice_edit/' . $invoice_id));
        }

        // Lấy items cũ để đổ ra form
        $items = $this->model->get_invoice_items($invoice_id);

        $data = [
            'invoice'    => $invoice,
            'student'    => $student,
            'items'      => $items,
            'student_id' => $invoice['student_id'],
            'title'      => 'Chỉnh sửa hóa đơn #' . ($invoice['invoice_code'] ?? (int)$invoice_id),
        ];

        $this->load->view('internship_management/student_client/invoice_edit', $data);
    }

    /* ============================================================================
        5. KÝ ONLINE HÓA ĐƠN
    ============================================================================ */
    public function invoice_sign($invoice_id = null)
    {
        if (!$invoice_id) show_404();

        $invoice = $this->model->get_invoice($invoice_id);
        if (!$invoice) show_404();

        $student = $this->model->get_student($invoice['student_id']);

        if ($this->input->post()) {

            $this->model->add_signature([
                'rel_type'  => 'invoice',
                'rel_id'    => $invoice_id,
                'signed_by' => $this->input->post('signed_by'),
                'signed_at' => date('Y-m-d H:i:s'),
            ]);

            $this->model->update_invoice_status($invoice_id, 'signed');

            set_alert('success', 'Đã ký online hóa đơn.');
            redirect(admin_url('internship_management/student_client/invoice_view/' . $invoice_id));
        }

        $data = [
            'invoice' => $invoice,
            'student' => $student,
            'title'   => 'Ký hóa đơn #' . ($invoice['invoice_code'] ?? (int)$invoice_id),
        ];

        $this->load->view('internship_management/student_client/invoice_sign', $data);
    }

    /* ============================================================================
        6. DANH SÁCH HỢP ĐỒNG
    ============================================================================ */
    public function contracts($student_id = null)
    {
        if (!$student_id) show_404();
        $student = $this->model->get_student($student_id);
        if (!$student) show_404();

        $data = [
            'student_id' => $student_id,
            'student'    => $student,
            'contracts'  => $this->model->get_contracts($student_id),
            'title'      => 'Hợp đồng – ' . ($student['full_name'] ?? ''),
        ];

        $this->load->view('internship_management/student_client/contracts', $data);
    }

    /* ============================================================================
        7. TẠO HỢP ĐỒNG
    ============================================================================ */
    public function contract_create($student_id = null)
    {
        if (!$student_id) show_404();

        $student = $this->model->get_student($student_id);
        if (!$student) show_404();

        if ($this->input->post()) {

            $insert = [
                'student_id'    => $student_id,
                'contract_name' => $this->input->post('contract_name'),
                'content'       => $this->input->post('content'),
                'status'        => $this->input->post('status') ?: 'draft',
                'datecreated'   => date('Y-m-d H:i:s'),
            ];

            $contract_id = $this->model->create_contract($insert);

            if ($contract_id) {
                set_alert('success', 'Tạo hợp đồng thành công.');
                redirect(admin_url('internship_management/student_client/contract_view/' . $contract_id));
            }

            set_alert('danger', 'Không thể tạo hợp đồng.');
        }

        $data = [
            'student' => $student,
            'title'   => 'Tạo hợp đồng – ' . ($student['full_name'] ?? ''),
        ];

        $this->load->view('internship_management/student_client/contract_create', $data);
    }

    /* ============================================================================
        8. XEM HỢP ĐỒNG
    ============================================================================ */
    public function contract_view($contract_id = null)
    {
        if (!$contract_id) show_404();

        $contract = $this->model->get_contract($contract_id);
        if (!$contract) show_404();

        $student = $this->model->get_student($contract['student_id']);

        $data = [
            'contract'   => $contract,
            'student'    => $student,
            'signatures' => $this->model->get_signatures('contract', $contract_id),
            'title'      => 'Hợp đồng: ' . ($contract['contract_name'] ?? (int)$contract_id),
        ];

        $this->load->view('internship_management/student_client/contract_view', $data);
    }

    /* ============================================================================
        9. KÝ ONLINE HỢP ĐỒNG
    ============================================================================ */
    public function contract_sign($contract_id = null)
    {
        if (!$contract_id) show_404();

        $contract = $this->model->get_contract($contract_id);
        if (!$contract) show_404();

        $student = $this->model->get_student($contract['student_id']);

        if ($this->input->post()) {

            $this->model->add_signature([
                'rel_type'  => 'contract',
                'rel_id'    => $contract_id,
                'signed_by' => $this->input->post('signed_by'),
                'signed_at' => date('Y-m-d H:i:s'),
            ]);

            $this->model->update_contract_status($contract_id, 'signed');

            set_alert('success', 'Đã ký online hợp đồng.');
            redirect(admin_url('internship_management/student_client/contract_view/' . $contract_id));
        }

        $data = [
            'contract' => $contract,
            'student'  => $student,
            'title'    => 'Ký hợp đồng: ' . ($contract['contract_name'] ?? (int)$contract_id),
        ];

        $this->load->view('internship_management/student_client/contract_sign', $data);
    }

    /* ============================================================================
        10. THÊM GHI CHÚ XỬ LÝ
    ============================================================================ */
    public function add_note($student_id)
    {
        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        if ($this->input->post()) {
            $note = [
                'student_id'  => $student_id,
                'staff_id'    => get_staff_user_id(),
                'note_type'   => $this->input->post('note_type'),
                'content'     => $this->input->post('content'),
                'reminder_at' => $this->input->post('reminder_at') ?: null,
            ];

            // Upload file đính kèm (hỗ trợ 1 hoặc nhiều file). Lưu: uploads/internship_notes/{student_id}/
            $hasFile = false;
            if (isset($_FILES['file'])) {
                if (is_array($_FILES['file']['name'])) {
                    $hasFile = !empty(array_filter($_FILES['file']['name']));
                } else {
                    $hasFile = !empty($_FILES['file']['name']);
                }
            }
            if ($hasFile) {
                $uploadedNames = $this->_handle_file_upload($student_id, 'file', 'internship_notes');
                if (!empty($uploadedNames)) {
                    // DB hiện chỉ có 1 cột file => lưu file đầu tiên
                    $note['file'] = $uploadedNames[0];
                }
            }

            // Write to tblinternship_notes if exists (to match view loader)
            $noteTable = null;
            foreach (['tblinternship_notes', db_prefix().'internship_notes', db_prefix().'tblinternship_notes'] as $t) {
                if ($this->db->table_exists($t)) { $noteTable = $t; break; }
            }
            if ($noteTable) {
                $row = [];
                if ($this->db->field_exists('student_id', $noteTable)) $row['student_id'] = (int)$student_id;
                if ($this->db->field_exists('staff_id', $noteTable))   $row['staff_id']   = (int)get_staff_user_id();
                if ($this->db->field_exists('content', $noteTable))    $row['content']    = (string)$note['content'];
                elseif ($this->db->field_exists('note', $noteTable))   $row['note']       = (string)$note['content'];
                if ($this->db->field_exists('note_type', $noteTable) && isset($note['note_type'])) $row['note_type'] = (string)$note['note_type'];
                if ($this->db->field_exists('file', $noteTable) && isset($note['file'])) $row['file'] = (string)$note['file'];
                foreach (['created_at','datecreated','created','date_added'] as $c) {
                    if ($this->db->field_exists($c, $noteTable)) { $row[$c] = date('Y-m-d H:i:s'); break; }
                }
                if (!empty($row)) { $this->db->insert($noteTable, $row); }
            } else {
                // fallback to existing notes service
                $this->notes->add($note);
            }

            // Activity log
            $preview = mb_substr(trim((string)$note['content']), 0, 120);
            $this->model->add_log((int)$student_id, 'Thêm ghi chú: ' . $preview, 'add_note');
        }

        redirect(admin_url('internship_management/student_client/view/' . $student_id . '#tab_notes'));
    }
    
    /* ============================================================================
        SỬA / XÓA GHI CHÚ XỬ LÝ
    ============================================================================ */

    private function _student_note_tables()
    {
        return [
            'tblinternship_notes',
            db_prefix().'internship_notes',
            db_prefix().'tblinternship_notes',
        ];
    }

    private function _find_student_note_row($student_id, $note_id)
    {
        $student_id = (int)$student_id;
        $note_id    = (int)$note_id;

        foreach ($this->_student_note_tables() as $table) {
            if (!$this->db->table_exists($table)) {
                continue;
            }

            if (!$this->db->field_exists('id', $table) || !$this->db->field_exists('student_id', $table)) {
                continue;
            }

            $row = $this->db
                ->where('id', $note_id)
                ->where('student_id', $student_id)
                ->get($table)
                ->row_array();

            if (!empty($row)) {
                return [
                    'table' => $table,
                    'row'   => $row,
                ];
            }
        }

        return null;
    }

    private function _note_content_column($table)
    {
        foreach (['content','note','description','message'] as $c) {
            if ($this->db->field_exists($c, $table)) {
                return $c;
            }
        }

        return null;
    }

    private function _note_file_column($table)
    {
        foreach (['file','attachment','file_name','filename'] as $c) {
            if ($this->db->field_exists($c, $table)) {
                return $c;
            }
        }

        return null;
    }

    private function _delete_student_note_file($student_id, $file_name)
    {
        $student_id = (int)$student_id;
        $file_name  = basename((string)$file_name);

        if ($student_id <= 0 || $file_name === '') {
            return;
        }

        $path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'internship_notes' . DIRECTORY_SEPARATOR . $student_id . DIRECTORY_SEPARATOR . $file_name;

        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function update_note($student_id, $note_id)
    {
        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        $student_id = (int)$student_id;
        $note_id    = (int)$note_id;

        if (!$this->input->post()) {
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_notes'));
        }

        $found = $this->_find_student_note_row($student_id, $note_id);
        if (!$found) {
            set_alert('danger', 'Không tìm thấy ghi chú cần sửa.');
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_notes'));
        }

        $table = $found['table'];
        $old   = $found['row'];

        $contentCol = $this->_note_content_column($table);
        if (!$contentCol) {
            set_alert('danger', 'Bảng ghi chú không có cột nội dung để sửa.');
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_notes'));
        }

        $data = [
            $contentCol => trim((string)$this->input->post('content', true)),
        ];

        if ($this->db->field_exists('note_type', $table)) {
            $data['note_type'] = $this->input->post('note_type', true) ?: 'internal';
        } elseif ($this->db->field_exists('type', $table)) {
            $data['type'] = $this->input->post('note_type', true) ?: 'internal';
        }

        if ($this->db->field_exists('reminder_at', $table)) {
            $reminder = $this->input->post('reminder_at', true);
            $data['reminder_at'] = $reminder ? $reminder : null;
        }

        foreach (['updated_at','dateupdated','modified_at'] as $c) {
            if ($this->db->field_exists($c, $table)) {
                $data[$c] = date('Y-m-d H:i:s');
                break;
            }
        }

        $fileCol = $this->_note_file_column($table);
        $hasNewFile = false;
        if (isset($_FILES['file'])) {
            if (is_array($_FILES['file']['name'])) {
                $hasNewFile = !empty(array_filter($_FILES['file']['name']));
            } else {
                $hasNewFile = !empty($_FILES['file']['name']);
            }
        }

        if ($hasNewFile && $fileCol) {
            $uploadedNames = $this->_handle_file_upload($student_id, 'file', 'internship_notes');
            if (!empty($uploadedNames)) {
                $oldFile = !empty($old[$fileCol]) ? (string)$old[$fileCol] : '';
                $data[$fileCol] = $uploadedNames[0];

                if ($oldFile !== '') {
                    $this->_delete_student_note_file($student_id, $oldFile);
                }
            }
        }

        $this->db->where('id', $note_id)->update($table, $data);

        if (isset($this->model) && method_exists($this->model, 'add_log')) {
            $this->model->add_log($student_id, 'Sửa ghi chú #' . $note_id, 'update_note');
        }

        set_alert('success', 'Đã cập nhật ghi chú.');
        redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_notes'));
    }

    public function delete_note($student_id, $note_id)
    {
        if (!has_permission('internship_management', '', 'delete')) {
            access_denied();
        }

        $student_id = (int)$student_id;
        $note_id    = (int)$note_id;

        $found = $this->_find_student_note_row($student_id, $note_id);
        if (!$found) {
            set_alert('danger', 'Không tìm thấy ghi chú cần xóa.');
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_notes'));
        }

        $table   = $found['table'];
        $row     = $found['row'];
        $fileCol = $this->_note_file_column($table);
        $file    = $fileCol && !empty($row[$fileCol]) ? (string)$row[$fileCol] : '';

        $this->db->where('id', $note_id)->delete($table);

        if ($this->db->affected_rows() > 0) {
            if ($file !== '') {
                $this->_delete_student_note_file($student_id, $file);
            }

            if (isset($this->model) && method_exists($this->model, 'add_log')) {
                $this->model->add_log($student_id, 'Xóa ghi chú #' . $note_id, 'delete_note');
            }

            set_alert('success', 'Đã xóa ghi chú.');
        } else {
            set_alert('danger', 'Không thể xóa ghi chú.');
        }

        redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_notes'));
    }

    /**
     * Upload helper nội bộ (tránh phụ thuộc hàm global handle_file_upload()).
     * - Hỗ trợ input đơn hoặc multiple (file / file[])
     * - Trả về danh sách file_name đã upload (đã sanitize)
     */
    private function _handle_file_upload($rel_id, $input_name, $folder)
    {
        $rel_id = (int)$rel_id;
        $uploaded = [];

        if (!isset($_FILES[$input_name])) {
            return $uploaded;
        }

        $basePath = FCPATH . 'uploads/' . trim($folder, '/');
        $targetPath = $basePath . '/' . $rel_id . '/';
        if (!is_dir($targetPath)) {
            @mkdir($targetPath, 0755, true);
        }

        $this->load->library('upload');

        $config = [
            'upload_path'   => $targetPath,
            'allowed_types' => '*',
            'max_size'      => 20480, // 20MB
            'overwrite'     => false,
        ];

        $fileData = $_FILES[$input_name];
        $isMulti = is_array($fileData['name']);
        $count = $isMulti ? count($fileData['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $name = $isMulti ? ($fileData['name'][$i] ?? '') : ($fileData['name'] ?? '');
            if (empty($name)) {
                continue;
            }

            $tmp = $isMulti ? ($fileData['tmp_name'][$i] ?? '') : ($fileData['tmp_name'] ?? '');
            if (empty($tmp)) {
                continue;
            }

            // Map lại $_FILES cho CI upload
            $_FILES['_sc_upload'] = [
                'name'     => $name,
                'type'     => $isMulti ? ($fileData['type'][$i] ?? '') : ($fileData['type'] ?? ''),
                'tmp_name' => $tmp,
                'error'    => $isMulti ? ($fileData['error'][$i] ?? 0) : ($fileData['error'] ?? 0),
                'size'     => $isMulti ? ($fileData['size'][$i] ?? 0) : ($fileData['size'] ?? 0),
            ];

            $safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename((string)$name));
            $config['file_name'] = time() . '_' . $safe;
            $this->upload->initialize($config);

            if ($this->upload->do_upload('_sc_upload')) {
                $data = $this->upload->data();
                if (!empty($data['file_name'])) {
                    $uploaded[] = $data['file_name'];
                }
            }
        }

        unset($_FILES['_sc_upload']);
        return $uploaded;
    }

    /* ============================================================================
        UPLOAD TÀI LIỆU HỒ SƠ (PRO)
        Lưu tại: uploads/internship_documents/{student_id}/
    ============================================================================ */
    public function upload_document($student_id)
    {
        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        $student_id = (int)$student_id;

        if (!$this->input->post()) {
            redirect(admin_url('internship_management/student_client/view/' . $student_id . '#tab_documents'));
        }

        if (empty($_FILES['file']['name'])) {
            set_alert('warning', _l('Vui lòng chọn file.'));
            redirect(admin_url('internship_management/student_client/view/' . $student_id . '#tab_documents'));
        }

        // NOTE: view submits name="doc_type"
        $doc_type = $this->input->post('doc_type') ?: ($this->input->post('type') ?: 'Khác');

        $uploaded = $this->_internship_upload_student_document($student_id, 'file');
        if (!$uploaded || empty($uploaded['file_name'])) {
            set_alert('danger', _l('Tải lên thất bại, vui lòng thử lại.'));
            redirect(admin_url('internship_management/student_client/view/' . $student_id . '#tab_documents'));
        }

        // ------------------------------------------------------------
        // Save DB
        // Prefer tblinternship_files because existing UI noted "Nguồn dữ liệu: tblinternship_files"
        // But auto-detect schema (student_id vs rel_id; doc_type vs type; staff columns; rel_type etc.)
        // ------------------------------------------------------------
        $tables = [
            db_prefix().'internship_files',
            db_prefix().'internship_documents',
            db_prefix().'internship_student_documents',
        ];

        foreach ($tables as $t) {
            if (!$this->db->table_exists($t)) {
                continue;
            }

            // detect id column
            $idCol = null;
            foreach (['student_id','rel_id','related_id'] as $c) {
                if ($this->db->field_exists($c, $t)) { $idCol = $c; break; }
            }

            // detect rel_type column (optional)
            $relTypeCol = null;
            foreach (['rel_type','related_type','type_rel'] as $c) {
                if ($this->db->field_exists($c, $t)) { $relTypeCol = $c; break; }
            }

            // file column
            $fileCol = null;
            foreach (['file_name', 'file', 'attachment', 'filename'] as $c) {
                if ($this->db->field_exists($c, $t)) { $fileCol = $c; break; }
            }

            // file path column (optional)
            $pathCol = null;
            foreach (['file_path','path','filepath','full_path'] as $c) {
                if ($this->db->field_exists($c, $t)) { $pathCol = $c; break; }
            }

            // document type column
            $typeCol = null;
            foreach (['doc_type','document_type','file_type','type','category'] as $c) {
                if ($this->db->field_exists($c, $t)) { $typeCol = $c; break; }
            }

            // staff columns
            $staffIdCol = null;
            foreach (['staff_id', 'addedfrom', 'created_by'] as $c) {
                if ($this->db->field_exists($c, $t)) { $staffIdCol = $c; break; }
            }
            $staffNameCol = $this->db->field_exists('staff_name', $t) ? 'staff_name' : null;

            // created time
            $createdCol = null;
            foreach (['created_at','datecreated','created','date_added','dateupload','date_uploaded'] as $c) {
                if ($this->db->field_exists($c, $t)) { $createdCol = $c; break; }
            }

            // If table doesn't have minimum columns, skip
            if (!$idCol || !$fileCol) {
                continue;
            }

            $row = [
                $idCol   => $student_id,
                $fileCol => $uploaded['file_name'],
            ];

            if ($pathCol) {
                // store relative path for display/download
                $row[$pathCol] = 'uploads/internship_documents/' . $student_id . '/' . $uploaded['file_name'];
            }

            if ($typeCol) {
                $row[$typeCol] = $doc_type;
            }

            // rel_type: try to reuse existing rel_type for this student if any
            if ($relTypeCol) {
                $existingRelType = null;
                // only try if table has rel_id/rel_type style
                try {
                    $this->db->select($relTypeCol);
                    $this->db->where($idCol, $student_id);
                    $this->db->where($relTypeCol.' IS NOT NULL', null, false);
                    $this->db->order_by($relTypeCol, 'asc');
                    $q = $this->db->get($t, 1)->row_array();
                    if (!empty($q[$relTypeCol])) { $existingRelType = $q[$relTypeCol]; }
                } catch (\Throwable $e) {}
                $row[$relTypeCol] = $existingRelType ?: 'student_client';
            }

            if ($staffIdCol) {
                $row[$staffIdCol] = (int)get_staff_user_id();
            }
            if ($staffNameCol) {
                $row[$staffNameCol] = get_staff_full_name(get_staff_user_id());
            }
            if ($createdCol) {
                $row[$createdCol] = date('Y-m-d H:i:s');
            }

            $this->db->insert($t, $row);
            break; // insert into first compatible table
        }

        // Log
        if (isset($this->model) && method_exists($this->model, 'add_log')) {
            $this->model->add_log($student_id, 'Upload tài liệu: ' . $uploaded['file_name'], 'upload_document');
        }

        set_alert('success', _l('Đã tải lên tài liệu.'));
        redirect(admin_url('internship_management/student_client/view/' . $student_id . '#tab_documents'));
    }
    
    /* ============================================================================
        SỬA / XÓA TÀI LIỆU HỒ SƠ
    ============================================================================ */

    private function _student_document_tables()
    {
        return [
            db_prefix().'internship_documents',
            db_prefix().'internship_student_documents',
            db_prefix().'internship_files',
        ];
    }

    private function _find_student_document_row($student_id, $document_id)
    {
        $student_id   = (int)$student_id;
        $document_id  = (int)$document_id;

        foreach ($this->_student_document_tables() as $table) {
            if (!$this->db->table_exists($table)) {
                continue;
            }

            if (!$this->db->field_exists('id', $table)) {
                continue;
            }

            $idCol = null;
            foreach (['student_id','rel_id','related_id'] as $c) {
                if ($this->db->field_exists($c, $table)) {
                    $idCol = $c;
                    break;
                }
            }

            if (!$idCol) {
                continue;
            }

            $row = $this->db
                ->where('id', $document_id)
                ->where($idCol, $student_id)
                ->get($table)
                ->row_array();

            if (!empty($row)) {
                return [
                    'table'  => $table,
                    'id_col' => $idCol,
                    'row'    => $row,
                ];
            }
        }

        return null;
    }

    private function _document_file_name_from_row($row)
    {
        foreach (['file_name','file','attachment','filename'] as $c) {
            if (!empty($row[$c])) {
                return (string)$row[$c];
            }
        }

        return '';
    }

    private function _document_type_column($table)
    {
        foreach (['doc_type','document_type','file_type','type','category'] as $c) {
            if ($this->db->field_exists($c, $table)) {
                return $c;
            }
        }

        return null;
    }

    private function _delete_student_document_file($student_id, $file_name)
    {
        $student_id = (int)$student_id;
        $file_name  = basename((string)$file_name);

        if ($student_id <= 0 || $file_name === '') {
            return;
        }

        $path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'internship_documents' . DIRECTORY_SEPARATOR . $student_id . DIRECTORY_SEPARATOR . $file_name;

        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function update_document($student_id, $document_id)
    {
        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        $student_id  = (int)$student_id;
        $document_id = (int)$document_id;

        if (!$this->input->post()) {
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_documents'));
        }

        $found = $this->_find_student_document_row($student_id, $document_id);
        if (!$found) {
            set_alert('danger', 'Không tìm thấy tài liệu cần sửa.');
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_documents'));
        }

        $table   = $found['table'];
        $typeCol = $this->_document_type_column($table);

        if (!$typeCol) {
            set_alert('warning', 'Bảng tài liệu hiện tại không có cột loại tài liệu để sửa.');
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_documents'));
        }

        $docType = trim((string)$this->input->post('doc_type', true));
        if ($docType === '') {
            $docType = 'Khác';
        }

        $this->db->where('id', $document_id)->update($table, [
            $typeCol => $docType,
        ]);

        if (isset($this->model) && method_exists($this->model, 'add_log')) {
            $this->model->add_log($student_id, 'Sửa loại tài liệu: #' . $document_id . ' -> ' . $docType, 'update_document');
        }

        set_alert('success', 'Đã cập nhật tài liệu.');
        redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_documents'));
    }

    public function delete_document($student_id, $document_id)
    {
        if (!has_permission('internship_management', '', 'delete')) {
            access_denied();
        }

        $student_id  = (int)$student_id;
        $document_id = (int)$document_id;

        $found = $this->_find_student_document_row($student_id, $document_id);
        if (!$found) {
            set_alert('danger', 'Không tìm thấy tài liệu cần xóa.');
            redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_documents'));
        }

        $table = $found['table'];
        $row   = $found['row'];
        $file  = $this->_document_file_name_from_row($row);

        $this->db->where('id', $document_id)->delete($table);

        if ($this->db->affected_rows() > 0) {
            $this->_delete_student_document_file($student_id, $file);

            if (isset($this->model) && method_exists($this->model, 'add_log')) {
                $this->model->add_log($student_id, 'Xóa tài liệu: ' . $file, 'delete_document');
            }

            set_alert('success', 'Đã xóa tài liệu.');
        } else {
            set_alert('danger', 'Không thể xóa tài liệu.');
        }

        redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_documents'));
    }
    /**
     * Upload tài liệu hồ sơ (module-safe)
     */
    private function _internship_upload_student_document(int $student_id, string $field): array|false
    {
        if (empty($_FILES[$field]['name'])) {
            return false;
        }

        $baseDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'internship_documents' . DIRECTORY_SEPARATOR . $student_id . DIRECTORY_SEPARATOR;
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        $original = $_FILES[$field]['name'];
        $tmp      = $_FILES[$field]['tmp_name'];

        $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $original);
        $filename = time() . '_' . $safeName;
        $dest     = $baseDir . $filename;

        if (@move_uploaded_file($tmp, $dest)) {
            return ['file_name' => $filename, 'full_path' => $dest];
        }

        return false;
    }


    /* ============================================================================
        ĐỒNG BỘ CRM (PERFEX)
        - Tạo/Update customer trong CRM
        - Lưu client_id/crm_client_id về hồ sơ sinh viên
    ============================================================================ */
    /**
     * Alias for legacy routes/buttons
     */
    public function push_to_crm($student_id)
    {
        return $this->push_crm($student_id);
    }

    public function push_crm($student_id)
    {
        if (!has_permission('internship_management', '', 'edit')) {
            access_denied();
        }

        $student_id = (int)$student_id;

        // Lấy hồ sơ sinh viên từ DB
        // Lưu ý: ID trên URL của student_client thường là "application_id" (không phải student_id).
        // Vì vậy ưu tiên lấy hồ sơ từ bảng internship_applications theo id trước.
        $profileTable = $this->db->table_exists(db_prefix().'internship_applications') ? db_prefix().'internship_applications' : null;

        $studentTable = null;
        $student = null;

        if ($profileTable) {
            $student = $this->db->get_where($profileTable, ['id' => $student_id])->row_array();
        }

        // Fallback: nếu không có bảng applications hoặc không có record, thử dò các bảng sinh viên
        if (!$student) {
            foreach ([db_prefix().'internship_students', db_prefix().'tblinternship_students', db_prefix().'internship_student'] as $t) {
                if ($this->db->table_exists($t)) { $studentTable = $t; break; }
            }

            if ($studentTable) {
                // dò khóa chính
                $keyCol = $this->db->field_exists('id', $studentTable) ? 'id' : ($this->db->field_exists('student_id', $studentTable) ? 'student_id' : 'id');
                $student = $this->db->get_where($studentTable, [$keyCol => $student_id])->row_array();
            }
        }

        if (!$student) {
            set_alert('warning', 'Không tìm thấy hồ sơ sinh viên.');
            redirect(admin_url('internship_management/student_client/view/'.$student_id));
        }

        // Map dữ liệu cơ bản (tự dò nhiều key vì schema khác nhau)
        $pick = function(array $arr, array $keys) {
            foreach ($keys as $k) {
                if (isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
                    return $arr[$k];
                }
            }
            return '';
        };

        $name  = $pick($student, ['name','full_name','student_name','fullname','ho_ten']);
        $email = $pick($student, ['email','student_email','email_address','personal_email','mail','gmail','email_sv']);
        $phone = $pick($student, ['phonenumber','phone','mobile','student_phone','phone_student','sdt','sdt_sinh_vien','student_phone_number']);
        $addr  = $pick($student, ['address','current_address','dia_chi','student_address']);

        $client_id = (int)($student['client_id'] ?? ($student['crm_client_id'] ?? ($student['userid'] ?? 0)));

// Nếu chưa có client_id trong hồ sơ => dò CRM để tránh tạo trùng
if ($client_id <= 0) {
    // 1) Dò theo email trong tblcontacts (email là unique nhất)
    if (!empty($email) && $this->db->table_exists(db_prefix().'contacts') && $this->db->field_exists('email', db_prefix().'contacts')) {
        $ct = $this->db->get_where(db_prefix().'contacts', ['email' => $email])->row_array();
        if ($ct && !empty($ct['userid'])) {
            $client_id = (int)$ct['userid'];
        }
    }

    // 2) Dò theo số điện thoại trong tblclients
    if ($client_id <= 0 && !empty($phone) && $this->db->table_exists(db_prefix().'clients') && $this->db->field_exists('phonenumber', db_prefix().'clients')) {
        // chuẩn hóa số: bỏ space/dot/dash
        $phone_norm = preg_replace('/[^0-9]/', '', (string)$phone);
        if ($phone_norm !== '') {
            // so khớp trực tiếp
            $this->db->from(db_prefix().'clients');
            $this->db->where('REPLACE(REPLACE(REPLACE(phonenumber," ",""),".",""),"-","") =', $phone_norm, false);
            $this->db->order_by('userid', 'DESC');
            $this->db->limit(1);
            $cl = $this->db->get()->row_array();
            if ($cl && !empty($cl['userid'])) {
                $client_id = (int)$cl['userid'];
            }
        }
    }
}

// Nếu vẫn chưa có client_id và đã từng đẩy CRM từ application khác cùng email/phone
// thì luôn ưu tiên cập nhật thay vì tạo mới (ở đây đã dò ở trên).

        // Load Perfex models
        if (!file_exists(APPPATH.'models/Clients_model.php')) {
            // Perfex core luôn có clients_model, nhưng fallback nếu bị đổi tên
            $this->load->model('clients_model');
        } else {
            $this->load->model('clients_model');
        }

        // Data for Perfex customer
        $customer = [
            'company'   => $name ?: ('Student #' . $student_id),
            'vat'       => '',
            'phonenumber' => $phone,
            'website'   => '',
            'address'   => $addr,
            'city'      => $student['city'] ?? '',
            'state'     => $student['state'] ?? '',
            'zip'       => $student['zip'] ?? '',
            'country'   => $student['country'] ?? 0,
        ];

        try {
            if ($client_id > 0) {
                // update
                $this->clients_model->update($customer, $client_id);
            } else {
                // create
                $client_id = (int)$this->clients_model->add($customer);
            }
        } catch (Throwable $e) {
            set_alert('danger', 'Đẩy CRM lỗi: '.$e->getMessage());
            redirect(admin_url('internship_management/student_client/view/'.$student_id));
        }

        if ($client_id <= 0) {
            set_alert('danger', 'Đẩy CRM thất bại (không tạo được khách hàng).');
            redirect(admin_url('internship_management/student_client/view/'.$student_id));
        }

        
        // ===== Đảm bảo nhóm khách hàng "Sinh Viên Internship" và gán cho khách hàng =====
        $groupName = 'Sinh Viên Internship';
        $groupsTable = null;
        $customerGroupRelTable = null;

        foreach ([db_prefix().'customers_groups', db_prefix().'tblcustomers_groups'] as $t) {
            if ($this->db->table_exists($t)) { $groupsTable = $t; break; }
        }
        foreach ([db_prefix().'customer_groups', db_prefix().'tblcustomer_groups'] as $t) {
            if ($this->db->table_exists($t)) { $customerGroupRelTable = $t; break; }
        }

        if ($groupsTable && $customerGroupRelTable) {
            // tìm/ tạo group
            $group = $this->db->get_where($groupsTable, ['name' => $groupName])->row_array();
            $groupId = (int)($group['id'] ?? 0);

            if ($groupId <= 0) {
                $this->db->insert($groupsTable, ['name' => $groupName]);
                $groupId = (int)$this->db->insert_id();
            }

            // gán group cho customer (tránh trùng)
            if ($groupId > 0) {
                $exists = null;
                // Perfex relation columns commonly: customer_id + groupid
                if ($this->db->field_exists('customer_id', $customerGroupRelTable) && $this->db->field_exists('groupid', $customerGroupRelTable)) {
                    $exists = $this->db->get_where($customerGroupRelTable, ['customer_id' => $client_id, 'groupid' => $groupId])->row();
                    if (!$exists) {
                        $this->db->insert($customerGroupRelTable, ['customer_id' => $client_id, 'groupid' => $groupId]);
                    }
                } elseif ($this->db->field_exists('customer_id', $customerGroupRelTable) && $this->db->field_exists('group_id', $customerGroupRelTable)) {
                    $exists = $this->db->get_where($customerGroupRelTable, ['customer_id' => $client_id, 'group_id' => $groupId])->row();
                    if (!$exists) {
                        $this->db->insert($customerGroupRelTable, ['customer_id' => $client_id, 'group_id' => $groupId]);
                    }
                } elseif ($this->db->field_exists('customer_id', $customerGroupRelTable) && $this->db->field_exists('groupid', $customerGroupRelTable)) {
                    // already handled
                } else {
                    // fallback best-effort: try common Perfex names
                    try {
                        $this->db->insert($customerGroupRelTable, ['customer_id' => $client_id, 'groupid' => $groupId]);
                    } catch (Throwable $e) { /* ignore */ }
                }
            }
        }


        // Tạo contact chính (email nằm ở contact trong Perfex)
        $email = trim((string)$email);
        if ($email !== '') {
            $contactsTable = db_prefix().'contacts';

            // Perfex core thường có contacts_model, nhưng để chắc chắn ta vẫn có fallback insert trực tiếp.
            try {
                $this->load->model('contacts_model');
            } catch (Throwable $e) {
                // ignore
            }

            // tránh tạo trùng: ưu tiên userid+email
            $existing = null;
            if ($this->db->table_exists($contactsTable) && $this->db->field_exists('email', $contactsTable) && $this->db->field_exists('userid', $contactsTable)) {
                $existing = $this->db->get_where($contactsTable, ['email' => $email, 'userid' => $client_id])->row_array();
            }

            // Data tối thiểu cho contact
            $contactInsert = [];
            if ($this->db->field_exists('userid', $contactsTable))      $contactInsert['userid'] = $client_id;
            if ($this->db->field_exists('is_primary', $contactsTable))  $contactInsert['is_primary'] = 1;
            if ($this->db->field_exists('firstname', $contactsTable))   $contactInsert['firstname'] = $name ?: ('Student '.$student_id);
            if ($this->db->field_exists('lastname', $contactsTable))    $contactInsert['lastname'] = '';
            if ($this->db->field_exists('email', $contactsTable))       $contactInsert['email'] = $email;
            if ($this->db->field_exists('phonenumber', $contactsTable)) $contactInsert['phonenumber'] = $phone;
            if ($this->db->field_exists('title', $contactsTable))       $contactInsert['title'] = 'Student';
            if ($this->db->field_exists('active', $contactsTable))      $contactInsert['active'] = 1;
            if ($this->db->field_exists('addedfrom', $contactsTable))   $contactInsert['addedfrom'] = (int)get_staff_user_id();

            // timestamp columns
            if ($this->db->field_exists('datecreated', $contactsTable)) $contactInsert['datecreated'] = date('Y-m-d H:i:s');
            if ($this->db->field_exists('created_at', $contactsTable))  $contactInsert['created_at']  = date('Y-m-d H:i:s');

            try {
                if ($existing) {
                    // update các field cơ bản
                    $upd = [];
                    foreach (['firstname','phonenumber','is_primary'] as $k) {
                        if (isset($contactInsert[$k])) $upd[$k] = $contactInsert[$k];
                    }
                    if (!empty($upd)) {
                        $this->db->where('id', (int)$existing['id']);
                        $this->db->update($contactsTable, $upd);
                    }
                } else {
                    // Thử dùng contacts_model trước (để Perfex xử lý logic nội bộ), nếu fail thì insert trực tiếp
                    $added = false;
                    if (isset($this->contacts_model) && $this->contacts_model && method_exists($this->contacts_model, 'add')) {
                        try {
                            // Một số phiên bản contacts_model->add($data, $customer_id, $send_email)
                            $tmp = $contactInsert;
                            // đảm bảo có các field thường dùng
                            if (!isset($tmp['userid'])) $tmp['userid'] = $client_id;
                            $res = @$this->contacts_model->add($tmp, $client_id, false);
                            $added = (bool)$res;
                        } catch (Throwable $e) {
                            $added = false;
                        }
                    }

                    if (!$added && !empty($contactInsert) && $this->db->table_exists($contactsTable)) {
                        $this->db->insert($contactsTable, $contactInsert);
                    }
                }
            } catch (Throwable $e) {
                // ignore contact errors
            }
        }

        // Lưu client_id về hồ sơ sinh viên (ưu tiên cập nhật bảng profile đang dùng)
        $update = [];
        $targetTable = null;

        if (!empty($profileTable) && $this->db->table_exists($profileTable)) {
            $targetTable = $profileTable;
        } elseif (!empty($studentTable) && $this->db->table_exists($studentTable)) {
            $targetTable = $studentTable;
        }

        if ($targetTable) {
            if ($this->db->field_exists('client_id', $targetTable)) $update['client_id'] = $client_id;
            if ($this->db->field_exists('crm_client_id', $targetTable)) $update['crm_client_id'] = $client_id;
            if ($this->db->field_exists('userid', $targetTable)) $update['userid'] = $client_id;

            if (!empty($update)) {
                $this->db->where('id', $student_id);
                $this->db->update($targetTable, $update);
            }
        }

        // Log
        if (isset($this->model) && method_exists($this->model, 'add_log')) {
            $this->model->add_log($student_id, 'Đẩy CRM: client_id='.$client_id, 'push_crm');
        }

        set_alert('success', 'Đã đồng bộ CRM thành công.');
        redirect(admin_url('internship_management/student_client/view/'.$student_id.'#tab_crm'));
    }

/**
 * Resolve CRM client_id for a given application/student view id.
 * In this module, student_client/view/{id} usually uses application_id.
 */
private function im_resolve_crm_client_id($id)
{
    $id = (int)$id;
    $tablesToCheck = [
        db_prefix().'internship_applications',
        db_prefix().'tblinternship_applications',
        db_prefix().'internship_students',
        db_prefix().'tblinternship_students',
        db_prefix().'internship_student',
    ];

    foreach ($tablesToCheck as $t) {
        if (!$this->db->table_exists($t)) continue;

        // determine key column
        $key = 'id';
        if (!$this->db->field_exists($key, $t)) {
            if ($this->db->field_exists('application_id', $t)) $key = 'application_id';
            elseif ($this->db->field_exists('student_id', $t)) $key = 'student_id';
        }

        $row = $this->db->get_where($t, [$key => $id])->row_array();
        if (!$row) continue;

        foreach (['client_id','crm_client_id','userid'] as $c) {
            if (isset($row[$c]) && (int)$row[$c] > 0) return (int)$row[$c];
        }
    }

    return 0;
}


/**
 * Resolve student avatar / ID photo URL for view rendering.
 * Supports absolute URLs, full paths, and relative upload paths/filenames.
 */
private function _resolve_student_avatar_url($student)
{
    $placeholder = base_url('modules/internship_management/assets/no-image.png');

    // Normalize to array for easier access
    if (is_object($student)) {
        $student = (array) $student;
    }
    if (!is_array($student)) {
        return $placeholder;
    }

    $candidates = [
        'avatar_url','avatar','photo','image','profile_image','student_image',
        'id_card_image','idcard_image','photo_idcard','card_photo','id_photo',
        'thumbnail','thumb'
    ];

    $raw = '';
    foreach ($candidates as $k) {
        if (!empty($student[$k])) { $raw = (string)$student[$k]; break; }
    }

    if (!$raw) {
        return $placeholder;
    }

    // Absolute URL
    if (preg_match('#^https?://#i', $raw)) {
        return $raw;
    }

    // Data URI
    if (strpos($raw, 'data:image') === 0) {
        return $raw;
    }

    // If already begins with base_url-like path
    if (strpos($raw, '/') === 0) {
        // absolute path on domain
        return base_url(ltrim($raw, '/'));
    }

    // If looks like "uploads/..."
    if (stripos($raw, 'uploads/') === 0) {
        $fs = FCPATH . $raw;
        if (@file_exists($fs)) return base_url($raw);
        // even if not exists, still return as url
        return base_url($raw);
    }

    // If just a filename, try common upload dirs
    $tryDirs = [
        'uploads/internship_avatar/',
        'uploads/internship_students/',
        'uploads/students/',
        'uploads/student_clients/',
        'uploads/clients/',
        'uploads/',
    ];

    foreach ($tryDirs as $dir) {
        $fs = FCPATH . $dir . $raw;
        if (@file_exists($fs)) {
            return base_url($dir . $raw);
        }
    }

    // Fallback: return as relative url
    return base_url($raw);
}


    /* ==========================================================================
        LOCAL LOADERS (FIX: tblinternship_files / notes / logs not showing)
        These bypass model mismatch and read from real tables by schema detection.
    ========================================================================= */

    private function _get_files_for_student(int $student_id): array
    {
        $candidates = [
            'tblinternship_files',
            db_prefix().'internship_files',
            db_prefix().'tblinternship_files',
            db_prefix().'internship_documents',
        ];
        foreach ($candidates as $t) {
            if (!$this->db->table_exists($t)) { continue; }

            // must have student_id + file_name at least
            $sidCol = $this->db->field_exists('student_id', $t) ? 'student_id' : null;
            if (!$sidCol) { continue; }
            $nameCol = null;
            foreach (['file_name','filename','file'] as $c) { if ($this->db->field_exists($c,$t)) { $nameCol=$c; break; } }
            if (!$nameCol) { continue; }

            $this->db->where($sidCol, $student_id);
            if ($this->db->field_exists('id', $t)) $this->db->order_by('id', 'DESC');
            $rows = $this->db->get($t)->result_array();

            // normalize
            foreach ($rows as &$r) {
                if (!isset($r['file_name']) && isset($r[$nameCol])) $r['file_name'] = $r[$nameCol];
                if (!isset($r['doc_type'])) {
                    foreach (['doc_type','file_type','type','category'] as $c) { if (isset($r[$c]) && $r[$c] !== '') { $r['doc_type']=$r[$c]; break; } }
                }
                if (!isset($r['created_at'])) {
                    foreach (['created_at','datecreated','dateupload','date_uploaded'] as $c) { if (isset($r[$c]) && $r[$c] !== '') { $r['created_at']=$r[$c]; break; } }
                }
                if (!isset($r['file_path'])) {
                    foreach (['file_path','path','filepath'] as $c) { if (isset($r[$c]) && $r[$c] !== '') { $r['file_path']=$r[$c]; break; } }
                }
            }
            unset($r);
            return $rows;
        }
        // fallback to model if exists
        if (isset($this->model) && method_exists($this->model, 'get_files')) {
            try { return (array)$this->model->get_files($student_id); } catch (\Throwable $e) {}
        }
        return [];
    }

    private function _get_notes_for_student(int $student_id): array
    {
        $candidates = [
            'tblinternship_notes',
            db_prefix().'internship_notes',
            db_prefix().'tblinternship_notes',
        ];
        foreach ($candidates as $t) {
            if (!$this->db->table_exists($t)) { continue; }
            $sidCol = $this->db->field_exists('student_id', $t) ? 'student_id' : null;
            if (!$sidCol) { continue; }

            $this->db->where($sidCol, $student_id);
            if ($this->db->field_exists('id', $t)) $this->db->order_by('id', 'DESC');
            $rows = $this->db->get($t)->result_array();

            foreach ($rows as &$r) {
                if (!isset($r['content'])) {
                    foreach (['content','note','description','message'] as $c) { if (isset($r[$c]) && $r[$c] !== '') { $r['content']=$r[$c]; break; } }
                }
                if (!isset($r['created_at'])) {
                    foreach (['created_at','datecreated','created','date_added'] as $c) { if (isset($r[$c]) && $r[$c] !== '') { $r['created_at']=$r[$c]; break; } }
                }
            }
            unset($r);
            return $rows;
        }
        if (isset($this->model) && method_exists($this->model, 'get_notes')) {
            try { return (array)$this->model->get_notes($student_id); } catch (\Throwable $e) {}
        }
        return [];
    }

    private function _get_logs_for_student(int $student_id): array
    {
        $candidates = [
            'tblinternship_logs',
            'tblinternship_activity_logs',
            db_prefix().'internship_logs',
            db_prefix().'tblinternship_logs',
        ];
        foreach ($candidates as $t) {
            if (!$this->db->table_exists($t)) { continue; }
            $sidCol = $this->db->field_exists('student_id', $t) ? 'student_id' : null;
            if (!$sidCol) { continue; }

            $this->db->where($sidCol, $student_id);
            if ($this->db->field_exists('id', $t)) $this->db->order_by('id', 'DESC');
            $rows = $this->db->get($t)->result_array();

            foreach ($rows as &$r) {
                if (!isset($r['content'])) {
                    foreach (['content','note','description','action','message'] as $c) { if (isset($r[$c]) && $r[$c] !== '') { $r['content']=$r[$c]; break; } }
                }
                if (!isset($r['created_at'])) {
                    foreach (['created_at','datecreated','created','date_added'] as $c) { if (isset($r[$c]) && $r[$c] !== '') { $r['created_at']=$r[$c]; break; } }
                }
            }
            unset($r);
            return $rows;
        }
        if (isset($this->model) && method_exists($this->model, 'get_logs')) {
            try { return (array)$this->model->get_logs($student_id); } catch (\Throwable $e) {}
        }
        return [];
    }
public function print_profile($student_id)
{
    if (!has_permission('internship_management', '', 'view')) {
        access_denied('internship_management');
    }

    $student_id = (int)$student_id;

    // resolve context để lấy đúng student (anh đã có hàm này)
    $ctx = $this->model->resolve_student_context($student_id);
    $student = $ctx['student'] ?? null;

    if (empty($student)) {
        show_404();
    }

    $data = [
        'student'    => $student,
        'student_id' => (int)($ctx['student_id'] ?? $student_id),
        'title'      => 'In hồ sơ - ' . ($student['full_name'] ?? ('#'.$student_id)),
    ];

    // view in A4 riêng
    $this->load->view('internship_management/student_client/print_profile', $data);

    }


}
