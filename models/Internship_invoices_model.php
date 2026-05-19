<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model quản lý hóa đơn Internship
 *
 * Bảng chính:  tblinternship_invoices
 * Bảng items:  tblinternship_invoice_items
 * Bảng ký:     tblinternship_invoice_signatures
 */
class Internship_invoices_model extends App_Model
{
    protected $table       = 'tblinternship_invoices';
    protected $items_table = 'tblinternship_invoice_items';
    protected $sign_table  = 'tblinternship_invoice_signatures';

    /** Danh sách cột hợp lệ của bảng hóa đơn (để chống đẩy sai cột) */
    protected $invoice_fields = [
        'id',
        'student_id',
        'invoice_code',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_total',
        'total',        // tổng cuối cùng
        'status',
        'description',
        'content',
        'token',
       'datecreated',
   'updated_at',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
     *  HELPER
     * ============================================================ */

    /** Tạo mã hóa đơn dạng HD000001/2025 */
    private function generate_invoice_code()
    {
        $year = date('Y');

        $last = $this->db->select('id')
            ->from($this->table)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        $next = $last ? ($last->id + 1) : 1;

        return sprintf('HD%06d/%s', $next, $year);
    }

    /**
     * Token cũ (giữ lại cho tương thích nếu chỗ nào đó còn dùng)
     * KHÔNG dùng cho hóa đơn mới nữa.
     */
    private function generate_token()
    {
        return app_generate_hash();
    }

    /**
     * Sinh token dạng ifk-abc123
     */
    private function generate_ifk_token()
    {
        $prefix = 'ifk-';
        // sinh 6 ký tự random a-z0-9
        $random = substr(
            strtolower(base_convert(bin2hex(random_bytes(4)), 16, 36)),
            0,
            6
        );

        return $prefix . $random;
    }

    /**
     * Sinh token public đẹp & đảm bảo unique
     */
    private function generate_public_token()
    {
        do {
            $token  = $this->generate_ifk_token();
            $exists = $this->db
                ->where('token', $token)
                ->count_all_results($this->table);
        } while ($exists > 0);

        return $token;
    }

    /** Lọc dữ liệu theo whitelist cột, đồng thời map amount -> total nếu có */
    private function clean_invoice_data($data)
    {
        if (!is_array($data)) {
            return [];
        }

        // Nếu code nào đó vẫn còn truyền 'amount' thì map sang 'total'
        if (isset($data['amount']) && !isset($data['total'])) {
            $data['total'] = $data['amount'];
        }
        unset($data['amount']); // bỏ hẳn để không đẩy lên SQL

        $clean = [];
        foreach ($this->invoice_fields as $col) {
            if (array_key_exists($col, $data)) {
                $clean[$col] = $data[$col];
            }
        }

        return $clean;
    }

    /* ============================================================
     *  GET
     * ============================================================ */

    public function get($id)
    {
        $invoice = $this->db->where('id', (int)$id)
            ->get($this->table)
            ->row_array();

        if (!$invoice) {
            return null;
        }

        $invoice['items']      = $this->get_items($invoice['id']);
        $invoice['signatures'] = $this->get_signatures($invoice['id']);

        // Chuẩn hóa số
        $invoice['subtotal']  = (float)($invoice['subtotal'] ?? 0);
        $invoice['tax_total'] = (float)($invoice['tax_total'] ?? 0);
        $invoice['total']     = (float)($invoice['total'] ?? 0);

        return $invoice;
    }

    public function get_by_token($token)
    {
        $invoice = $this->db->where('token', $token)
            ->get($this->table)
            ->row_array();

        if (!$invoice) {
            return null;
        }

        $invoice['items']      = $this->get_items($invoice['id']);
        $invoice['signatures'] = $this->get_signatures($invoice['id']);

        $invoice['subtotal']  = (float)($invoice['subtotal'] ?? 0);
        $invoice['tax_total'] = (float)($invoice['tax_total'] ?? 0);
        $invoice['total']     = (float)($invoice['total'] ?? 0);

        return $invoice;
    }

    public function get_by_student($student_id)
    {
        return $this->db
            ->where('student_id', (int)$student_id)
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result_array();
    }

    /* ============================================================
     *  ITEMS & SIGNATURES
     * ============================================================ */

    public function get_items($invoice_id)
    {
        return $this->db
            ->select('id, invoice_id, item_name, description, unit, qty, rate, tax_rate, amount')
            ->where('invoice_id', (int)$invoice_id)
            ->order_by('id', 'ASC')
            ->get($this->items_table)
            ->result_array();
    }

    public function get_signatures($invoice_id)
    {
        return $this->db
            ->where('invoice_id', (int)$invoice_id)
            ->order_by('signed_at', 'ASC')
            ->get($this->sign_table)
            ->result_array();
    }

    /* ============================================================
     *  TÍNH TỔNG
     * ============================================================ */

    private function calculate_totals($items)
    {
        $subtotal  = 0;
        $tax_total = 0;

        if (!is_array($items)) {
            $items = [];
        }

        foreach ($items as $it) {
            $qty      = isset($it['qty']) ? (float)$it['qty'] : 0;
            $rate     = isset($it['rate']) ? (float)$it['rate'] : 0;
            $tax_rate = isset($it['tax_rate']) ? (float)$it['tax_rate'] : 0;

            $line    = $qty * $rate;
            $tax_amt = $line * $tax_rate / 100;

            $subtotal  += $line;
            $tax_total += $tax_amt;
        }

        return [
            'subtotal'  => $subtotal,
            'tax_total' => $tax_total,
            'total'     => $subtotal + $tax_total,
        ];
    }

    /* ============================================================
     *  CREATE
     * ============================================================ */

    public function create($student_id, $data, $items = [])
    {
        $student_id = (int)$student_id;

        // ========== Base data ==========
        $insert = [
            'student_id'   => $student_id,
            'invoice_code' => $data['invoice_code'] ?? $this->generate_invoice_code(),
            'invoice_date' => $data['invoice_date'] ?? date('Y-m-d'),
            'due_date'     => $data['due_date'] ?? null,
            'description'  => $data['description'] ?? '',
            'content'      => $data['content'] ?? '',
            'status'       => $data['status'] ?? 'unpaid',
            'datecreated'   => date('Y-m-d H:i:s'),
        ];

        // ========== Token đẹp IFK, unique ==========
        // Nếu có token truyền vào thì dùng, không thì sinh mới
        if (!empty($data['token'])) {
            $insert['token'] = $data['token'];
        } else {
            $insert['token'] = $this->generate_public_token();
        }

        // ========== Tổng tiền ==========
        $totals = $this->calculate_totals($items);
        $insert = array_merge($insert, $totals);

        // Lọc cột hợp lệ
        $insert = $this->clean_invoice_data($insert);

        $this->db->insert($this->table, $insert);
        $invoice_id = $this->db->insert_id();

        if (!$invoice_id) {
            return false;
        }

        // Lưu items
        $this->save_items($invoice_id, $items);

        return $invoice_id;
    }

    /* ============================================================
     *  UPDATE (wrapper) – dùng chung
     * ============================================================ */

    /**
     * Wrapper để tương thích nếu chỗ khác gọi:
     * $this->Internship_invoices_model->update(...)
     */
    public function update($invoice_id, $data, $items = [])
    {
        return $this->update_invoice_full($invoice_id, $data, $items);
    }

    /**
     * Hàm đầy đủ: update invoice + items + tính lại tổng
     */
    public function update_invoice_full($invoice_id, $invoice_data, $items = [])
    {
        $invoice_id = (int)$invoice_id;
        if ($invoice_id <= 0) {
            return false;
        }

        if (!is_array($invoice_data)) {
            $invoice_data = [];
        }

        // Nếu hóa đơn cũ chưa có token → sinh token mới
        if (empty($invoice_data['token'])) {
            $current = $this->db->select('token')
                ->where('id', $invoice_id)
                ->get($this->table)
                ->row();

            if (!$current || empty($current->token)) {
                $invoice_data['token'] = $this->generate_public_token();
            }
        }

        // Tính lại tổng từ items (nếu có items)
        $totals = $this->calculate_totals($items);
        $invoice_data = array_merge($invoice_data, $totals);

        // Cập nhật thời gian sửa
        $invoice_data['updated_at'] = date('Y-m-d H:i:s');

        // Lọc cột hợp lệ + bỏ 'amount'
        $update = $this->clean_invoice_data($invoice_data);

        // Không có gì để update
        if (empty($update)) {
            return true;
        }

        // 1. Update bảng invoice
        $this->db->where('id', $invoice_id)
                 ->update($this->table, $update);

        // 2. Nếu có items: xóa cũ, insert mới
        if (is_array($items)) {
            $this->db->where('invoice_id', $invoice_id)
                     ->delete($this->items_table);

            $this->save_items($invoice_id, $items);
        }

        return true;
    }

    /* ============================================================
     *  SAVE ITEMS
     * ============================================================ */

    private function save_items($invoice_id, $items)
    {
        $invoice_id = (int)$invoice_id;

        if (!is_array($items)) {
            return;
        }

        foreach ($items as $it) {
            $name = trim($it['item_name'] ?? '');
            if ($name === '') {
                continue;
            }

            $qty      = isset($it['qty']) ? (float)$it['qty'] : 0;
            $rate     = isset($it['rate']) ? (float)$it['rate'] : 0;
            $tax_rate = isset($it['tax_rate']) ? (float)$it['tax_rate'] : 0;

            $line    = $qty * $rate;
            $tax_amt = $line * $tax_rate / 100;
            $amount  = $line + $tax_amt;

            $row = [
                'invoice_id'  => $invoice_id,
                'item_name'   => $name,
                'description' => $it['description'] ?? '',
                'unit'        => $it['unit'] ?? '',
                'qty'         => $qty,
                'rate'        => $rate,
                'tax_rate'    => $tax_rate,
                'amount'      => $amount,
            ];

            $this->db->insert($this->items_table, $row);
        }
    }

    /* ============================================================
     *  SIGNATURES
     * ============================================================ */

    public function add_signature($invoice_id, $signed_by)
    {
        $invoice_id = (int)$invoice_id;

        return $this->db->insert($this->sign_table, [
            'invoice_id' => $invoice_id,
            'signed_by'  => $signed_by,
            'signed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /* ============================================================
     *  DELETE
     * ============================================================ */

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id)->delete($this->table);
        $this->db->where('invoice_id', $id)->delete($this->items_table);
        $this->db->where('invoice_id', $id)->delete($this->sign_table);

        return true;
    }
}