<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Internship_payment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library(['session', 'app_modules']);
        $this->load->helper(['url', 'form']);
        $this->load->model('internship_management/Internship_invoices_model', 'inv');
        $this->load->model('payment_modes_model');
    }

    /**
     * POST /internship_payment/pay/{internship_invoice_id}
     */
    public function pay($internship_invoice_id = null)
    {
        /* ---- 1. Chỉ cho phép POST ---- */
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            return $this->_expired();
        }

        $internship_invoice_id = (int)$internship_invoice_id;
        if ($internship_invoice_id <= 0) show_404();

        /* ---- 2. Lấy invoice ---- */
        $invoice = $this->inv->get($internship_invoice_id);
        if (!$invoice) show_404();

        $invoice = (array)$invoice;

        /* ---- 3. Lấy gateway user chọn ---- */
        $gateway_id = trim((string)$this->input->post('payment_mode_id'));

        if ($gateway_id === '') {
            $this->_alert('danger', 'Vui lòng chọn phương thức thanh toán.');
            return $this->_redirect_back_public($invoice);
        }

        /* ---- 4. Kiểm tra gateway CRM đang bật ---- */
        $gateways = $this->payment_modes_model->get_payment_gateways(true);
        $selected = null;

        foreach ($gateways as $gw) {
            if ((string)$gw['id'] === $gateway_id) {
                $selected = $gw;
                break;
            }
        }

        if (!$selected) {
            $this->_alert('danger', 'Cổng thanh toán đang tắt hoặc không hợp lệ.');
            return $this->_redirect_back_public($invoice);
        }

        /* ---- 5. Lấy CRM Invoice ID (đã map) ---- */
        $crm_invoice_id = (int)($invoice['crm_invoice_id'] ?? 0);

        if ($crm_invoice_id <= 0) {
            $this->_alert('danger', 'Hóa đơn Internship chưa được liên kết với hóa đơn CRM.');
            return $this->_redirect_back_public($invoice);
        }

        /* ---- 6. Redirect sang gateway CRM chuẩn ---- */
        $redirect_url = site_url('gateways/' . $gateway_id . '/payment/' . $crm_invoice_id);

        redirect($redirect_url);
        exit;
    }

    /* ----- Helpers ----- */

    private function _expired()
    {
        $this->output->set_status_header(419);
        echo "<h2>419 - Page Expired</h2><p>Vui lòng quay lại trang hóa đơn và thử lại.</p>";
        return;
    }

    private function _public_url($invoice)
    {
        $invoice = (array)$invoice;

        if (!empty($invoice['token'])) {
            return site_url('invoice/' . $invoice['token']);
        }

        return site_url('invoice-id/' . $invoice['id']);
    }

    private function _redirect_back_public($invoice)
    {
        redirect($this->_public_url($invoice));
        exit;
    }

    private function _alert($type, $message)
    {
        if (function_exists('set_alert')) {
            set_alert($type, $message);
        } else {
            $this->session->set_flashdata('alert_type', $type);
            $this->session->set_flashdata('alert_message', $message);
        }
    }
}