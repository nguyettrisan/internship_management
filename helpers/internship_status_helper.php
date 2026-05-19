<?php

if (!function_exists('im_normalize_status')) {
    function im_normalize_status($status)
    {
        $raw = strtolower(trim((string)$status));
    
        // các giá trị rỗng / bẩn => xem như chưa cập nhật
        if ($raw === '' || in_array($raw, ['-', '—', '--', 'null', 'undefined', 'n/a', 'na'], true)) {
            return 'not_updated';
        }
    
        $status = str_replace([' ', '-'], '_', $raw);
        $status = preg_replace('/_+/', '_', $status);
    
        if ($status === '' || $status === '_') {
            return 'not_updated';
        }
    
        $map = [
            'not_updated'           => 'not_updated',
            'chua_cap_nhat'         => 'not_updated',
    
            'applied'               => 'applied',
            'submitted'             => 'applied',
            'da_nop_don'            => 'applied',
    
            'interview_scheduled'   => 'interview_scheduled',
            'scheduled_interview'   => 'interview_scheduled',
            'phong_van'             => 'interview_scheduled',
    
            'pass'                  => 'pass',
            'passed'                => 'pass',
            'dat'                   => 'pass',
    
            'fail'                  => 'fail',
            'failed'                => 'fail',
            'rejected'              => 'fail',
            'rot'                   => 'fail',
    
            'prepare_documents'     => 'docs_preparing',
            'preparing_documents'   => 'docs_preparing',
            'document_preparing'    => 'docs_preparing',
            'docs_preparing'        => 'docs_preparing',
            'dang_lam_ho_so'        => 'docs_preparing',
    
            'done_documents'        => 'docs_done',
            'docs_done'             => 'docs_done',
            'hoan_tat_ho_so'        => 'docs_done',
    
            'waiting_coe'           => 'coe_waiting',
            'coe_waiting'           => 'coe_waiting',
            'wait_coe'              => 'coe_waiting',
            'cho_coe'               => 'coe_waiting',
    
            'got_coe'               => 'has_coe',
            'has_coe'               => 'has_coe',
            'coe_done'              => 'has_coe',
            'coe_received'          => 'has_coe',
    
            'visa_processing'       => 'visa_processing',
            'dang_xu_ly_visa'       => 'visa_processing',
    
            'ticket_booking'        => 'ticket_booking',
            'book_ticket'           => 'ticket_booking',
            'mua_ve'                => 'ticket_booking',
    
            'pre_departure'         => 'pre_departure',
            'before_departure'      => 'pre_departure',
            'departure_waiting'     => 'pre_departure',
            'cho_xuat_canh'         => 'pre_departure',
    
            'entry'                 => 'entry',
            'entered'               => 'entry',
            'da_nhap_canh'          => 'entry',
    
            'in_japan'              => 'in_japan',
            'dang_o_nhat'           => 'in_japan',
    
            'returned'              => 'returned',
            'return'                => 'returned',
            've_nuoc'               => 'returned',
            'da_ve_nuoc'            => 'returned',
    
            'cancelled'             => 'cancelled',
            'canceled'              => 'cancelled',
            'huy'                   => 'cancelled',
            'da_huy'                => 'cancelled',
            
            'interview_passed'      => 'pass',
            'passed_interview'      => 'pass',
            'dau_phong_van'         => 'pass',
            
            'interview_fail'        => 'fail',
            'interview_failed'      => 'fail',
            'rot_phong_van'         => 'fail',
            
            'waiting_coe'           => 'coe_waiting',
            
            'coe_done'              => 'has_coe',
            
            'pre_return'            => 'returned',
            'returned_vn'           => 'returned',
            'back_vn'               => 'returned',
            
            'processing'            => 'processing',
            'in_progress'           => 'processing',
            'preparing'             => 'docs_preparing',
            'waiting_for_interview' => 'interview_scheduled',
            'waiting_for_visa'      => 'visa_processing',
            'waiting_for_entry'     => 'pre_departure',
            'entered'               => 'entry',
            
            'stopped'               => 'cancelled',
            'stop'                  => 'cancelled',
            'dung_ho_so'            => 'cancelled',
            'rejected'              => 'fail',
            
            'đã_hủy'                => 'cancelled',
            'đã_huỷ'                => 'cancelled',
            'hủy'                   => 'cancelled',
            'huỷ'                   => 'cancelled',
            
            'đang_ở_nhật'           => 'in_japan',
            'đã_về_nước'            => 'returned',
            'chờ_coe'               => 'coe_waiting',
            'chờ_kết_quả_coe'       => 'coe_waiting',
        ];
    
        return isset($map[$status]) ? $map[$status] : $status;
    }
}

if (!function_exists('im_status_label_vi')) {
    function im_status_label_vi($status)
    {
        $status = im_normalize_status($status);

        $labels = [
            'not_updated'         => 'Chưa cập nhật',
            'applied'             => 'Đã nộp đơn',
            'interview_scheduled' => 'Đã lên lịch phỏng vấn',
            'pass'                => 'Đạt',
            'fail'                => 'Rớt',
            'docs_preparing'      => 'Đang làm hồ sơ',
            'docs_done'           => 'Đã hoàn tất hồ sơ',
            'coe_waiting'         => 'Chờ kết quả COE',
            'has_coe'             => 'Đã có COE – chờ nhập cảnh',
            'visa_processing'     => 'Đang làm visa',
            'ticket_booking'      => 'Mua vé nhập cảnh',
            'pre_departure'       => 'Chuẩn bị bay',
            'entry'               => 'Đã nhập cảnh',
            'in_japan'            => 'Đang ở Nhật',
            'returned'            => 'Đã về nước',
            'cancelled'           => 'Đã hủy',
        ];

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}

if (!function_exists('im_status_order')) {
    function im_status_order($type = 'application')
    {
        $type = strtolower(trim((string)$type));

        if ($type === 'job_order') {
            return [
                'received',
                'sent_to_schools',
                'recruiting',
                'interview_scheduled',
                'interview_result',
                'docs_preparing',
                'docs_done',
                'coe_waiting',
                'has_coe',
                'entry',
                'done',
                'cancelled',
            ];
        }

        return [
            'applied',
            'interview_scheduled',
            'pass',
            'fail',
            'docs_preparing',
            'docs_done',
            'coe_waiting',
            'has_coe',
            'visa_processing',
            'ticket_booking',
            'pre_departure',
            'entry',
            'in_japan',
            'returned',
            'cancelled',
        ];
    }
}

if (!function_exists('im_status_meta')) {
    function im_status_meta($type, $status)
    {
        $status = im_normalize_status($status);

        $labels = [
            'received'            => ['label' => 'Tiếp nhận đơn', 'color' => 'primary'],
            'sent_to_schools'     => ['label' => 'Đã gửi đơn cho trường', 'color' => 'info'],
            'recruiting'          => ['label' => 'Đang tuyển ứng viên', 'color' => 'info'],
            'interview_result'    => ['label' => 'Trả KQ PV & làm hồ sơ', 'color' => 'warning'],
            'done'                => ['label' => 'Hoàn tất chương trình', 'color' => 'default'],

            'not_updated'         => ['label' => 'Chưa cập nhật', 'color' => 'default'],
            'applied'             => ['label' => 'Đã nộp đơn', 'color' => 'primary'],
            'interview_scheduled' => ['label' => 'Đã lên lịch phỏng vấn', 'color' => 'warning'],
            'pass'                => ['label' => 'Đạt', 'color' => 'success'],
            'fail'                => ['label' => 'Rớt', 'color' => 'danger'],
            'docs_preparing'      => ['label' => 'Đang làm hồ sơ', 'color' => 'warning'],
            'docs_done'           => ['label' => 'Đã hoàn tất hồ sơ', 'color' => 'success'],
            'coe_waiting'         => ['label' => 'Chờ kết quả COE', 'color' => 'default'],
            'has_coe'             => ['label' => 'Đã có COE – chờ nhập cảnh', 'color' => 'success'],
            'visa_processing'     => ['label' => 'Đang làm visa', 'color' => 'info'],
            'ticket_booking'      => ['label' => 'Mua vé nhập cảnh', 'color' => 'info'],
            'pre_departure'       => ['label' => 'Chuẩn bị bay', 'color' => 'warning'],
            'entry'               => ['label' => 'Đã nhập cảnh', 'color' => 'success'],
            'in_japan'            => ['label' => 'Đang ở Nhật', 'color' => 'success'],
            'returned'            => ['label' => 'Đã về nước', 'color' => 'default'],
            'cancelled'           => ['label' => 'Đã hủy', 'color' => 'danger'],
            'processing'          => ['label' => 'Đang xử lý', 'color' => 'warning'],
        ];

        return $labels[$status] ?? ['label' => (string)$status, 'color' => 'default'];
    }
}

if (!function_exists('im_status_list')) {
    function im_status_list($type = 'application')
    {
        $out = [];
        foreach (im_status_order($type) as $code) {
            $meta = im_status_meta($type, $code);
            $out[$code] = $meta['label'];
        }
        return $out;
    }
}

//
if (!function_exists('im_status_clean_key')) {
    function im_status_clean_key($value)
    {
        $value = strtolower(trim((string)$value));

        if ($value === '' || in_array($value, ['-', '—', '--', 'null', 'undefined', 'n/a', 'na'], true)) {
            return '';
        }

        $value = str_replace([' ', '-'], '_', $value);
        $value = preg_replace('/_+/', '_', $value);

        return trim($value, '_');
    }
}

if (!function_exists('im_interview_result_list')) {
    function im_interview_result_list()
    {
        return [
            ''     => '— Chưa đánh giá —',
            'pass' => 'Đạt',
            'fail' => 'Rớt',
        ];
    }
}

if (!function_exists('im_dossier_progress_list')) {
    function im_dossier_progress_list()
    {
        return [
            'not_updated'         => 'Chưa cập nhật',
            'applied'             => 'Ứng tuyển',
            'interview_scheduled' => 'Hẹn phỏng vấn',
            'docs_preparing'      => 'Đang làm hồ sơ',
            'docs_done'           => 'Hoàn thành hồ sơ',
            'coe_waiting'         => 'Đợi COE',
            'has_coe'             => 'Đã có COE',
            'visa_processing'     => 'Làm visa',
            'ticket_booking'      => 'Mua vé nhập cảnh',
            'pre_departure'       => 'Chuẩn bị bay',
            'entry'               => 'Đã nhập cảnh',
            'in_japan'            => 'Đang ở Nhật',
            'returned'            => 'Đã về nước',
            'cancelled'           => 'Đã huỷ',
        ];
    }
}

if (!function_exists('im_application_filter_status_list')) {
    function im_application_filter_status_list()
    {
        return [
            'applied'             => 'Đã nộp đơn',
            'interview_scheduled' => 'Đã lên lịch phỏng vấn',

            // Kết quả PV
            'pass'                => 'Đạt',
            'fail'                => 'Rớt',

            // Tiến độ hồ sơ
            'docs_preparing'      => 'Đang làm hồ sơ',
            'docs_done'           => 'Đã hoàn tất hồ sơ',
            'coe_waiting'         => 'Chờ kết quả COE',
            'has_coe'             => 'Đã có COE – chờ nhập cảnh',
            'visa_processing'     => 'Đang làm visa',
            'ticket_booking'      => 'Mua vé nhập cảnh',
            'pre_departure'       => 'Chuẩn bị bay',
            'entry'               => 'Đã nhập cảnh',
            'in_japan'            => 'Đang ở Nhật',
            'returned'            => 'Đã về nước',
            'cancelled'           => 'Đã hủy',
            'not_updated'         => 'Chưa cập nhật',
        ];
    }
}

if (!function_exists('im_status_color_hex')) {
    function im_status_color_hex($status)
    {
        $status = im_normalize_status($status);

        $colors = [
            'not_updated'         => '#94a3b8',
            'applied'             => '#00a6dc',
            'interview_scheduled' => '#2563eb',

            'pass'                => '#22c55e',
            'fail'                => '#ef4444',

            'docs_preparing'      => '#f59e0b',
            'docs_done'           => '#22c55e',
            'coe_waiting'         => '#6366f1',
            'has_coe'             => '#8b5cf6',
            'visa_processing'     => '#0ea5e9',
            'ticket_booking'      => '#fb7185',
            'pre_departure'       => '#f97316',

            'entry'               => '#00a6dc',
            'in_japan'            => '#96bc17',
            'returned'            => '#14b8a6',
            'cancelled'           => '#ef4444',

            'processing'          => '#f59e0b',
        ];

        return $colors[$status] ?? '#64748b';
    }
}

if (!function_exists('im_school_portal_status_options')) {
    function im_school_portal_status_options()
    {
        return ['' => 'Tất cả'] + im_application_filter_status_list();
    }
}

if (!function_exists('im_normalize_interview_result')) {
    function im_normalize_interview_result($value)
    {
        $key = im_status_clean_key($value);

        $map = [
            'pass'             => 'pass',
            'passed'           => 'pass',
            'dat'              => 'pass',
            'dau'              => 'pass',
            'interview_passed' => 'pass',
            'passed_interview' => 'pass',
            'dau_phong_van'    => 'pass',

            'fail'             => 'fail',
            'failed'           => 'fail',
            'rot'              => 'fail',
            'interview_fail'   => 'fail',
            'interview_failed' => 'fail',
            'rot_phong_van'    => 'fail',
            'rejected'         => 'fail',
        ];

        return $map[$key] ?? '';
    }
}

if (!function_exists('im_normalize_dossier_progress')) {
    function im_normalize_dossier_progress($value)
    {
        $key = im_status_clean_key($value);

        if ($key === '') {
            return 'not_updated';
        }

        $map = [
            'not_updated'       => 'not_updated',
            'chua_cap_nhat'     => 'not_updated',
            'not_update'        => 'not_updated',
            'notupdated'        => 'not_updated',

            'applied'           => 'applied',
            'submitted'         => 'applied',
            'ung_tuyen'         => 'applied',
            'da_nop_don'        => 'applied',

            'interview_scheduled' => 'interview_scheduled',
            'scheduled_interview' => 'interview_scheduled',
            'hen_phong_van'       => 'interview_scheduled',
            'phong_van'           => 'interview_scheduled',

            'docs_preparing'      => 'docs_preparing',
            'prepare_documents'   => 'docs_preparing',
            'preparing_documents' => 'docs_preparing',
            'document_preparing'  => 'docs_preparing',
            'dang_lam_ho_so'      => 'docs_preparing',
            'chuan_bi_ho_so'      => 'docs_preparing',

            'docs_done'           => 'docs_done',
            'done_documents'      => 'docs_done',
            'hoan_tat_ho_so'      => 'docs_done',
            'hoan_thanh_ho_so'    => 'docs_done',

            'coe_waiting'         => 'coe_waiting',
            'waiting_coe'         => 'coe_waiting',
            'wait_coe'            => 'coe_waiting',
            'cho_coe'             => 'coe_waiting',
            'doi_coe'             => 'coe_waiting',
            'cho_ket_qua_coe'     => 'coe_waiting',

            'has_coe'             => 'has_coe',
            'got_coe'             => 'has_coe',
            'coe_done'            => 'has_coe',
            'coe_received'        => 'has_coe',
            'coe_has'             => 'has_coe',
            'da_co_coe'           => 'has_coe',

            'visa_processing'     => 'visa_processing',
            'doing_visa'          => 'visa_processing',
            'lam_visa'            => 'visa_processing',
            'dang_lam_visa'       => 'visa_processing',

            'ticket_booking'      => 'ticket_booking',
            'book_ticket'         => 'ticket_booking',
            'buy_ticket'          => 'ticket_booking',
            'mua_ve'              => 'ticket_booking',
            'mua_ve_nhap_canh'    => 'ticket_booking',

            'pre_departure'       => 'pre_departure',
            'before_departure'    => 'pre_departure',
            'departure_waiting'   => 'pre_departure',
            'prepare_flight'      => 'pre_departure',
            'chuan_bi_bay'        => 'pre_departure',
            'cho_xuat_canh'       => 'pre_departure',

            'entry'               => 'entry',
            'entered'             => 'entry',
            'da_nhap_canh'        => 'entry',

            'in_japan'            => 'in_japan',
            'dang_o_nhat'         => 'in_japan',

            'returned'            => 'returned',
            'return'              => 'returned',
            'pre_return'          => 'returned',
            'returned_vn'         => 'returned',
            'back_vn'             => 'returned',
            've_nuoc'             => 'returned',
            'da_ve_nuoc'          => 'returned',

            'cancelled'           => 'cancelled',
            'canceled'            => 'cancelled',
            'huy'                 => 'cancelled',
            'huỷ'                 => 'cancelled',
            'da_huy'              => 'cancelled',
            'da_huỷ'              => 'cancelled',
            'stopped'             => 'cancelled',
            'stop'                => 'cancelled',
            'dung_ho_so'          => 'cancelled',

            // Dữ liệu cũ từng nhét kết quả PV vào progress
            'pass'                => 'docs_preparing',
            'passed'              => 'docs_preparing',
            'interview_passed'    => 'docs_preparing',

            'fail'                => 'cancelled',
            'failed'              => 'cancelled',
            'interview_fail'      => 'cancelled',
            'rejected'            => 'cancelled',
        ];

        return $map[$key] ?? 'not_updated';
    }
}

if (!function_exists('im_dossier_progress_aliases')) {
    function im_dossier_progress_aliases($progress)
    {
        $progress = im_normalize_dossier_progress($progress);

        $map = [
            'not_updated' => ['', null, 'not_updated', 'chua_cap_nhat', 'not_update', 'notupdated'],
            'applied' => ['applied', 'submitted', 'ung_tuyen', 'da_nop_don'],
            'interview_scheduled' => ['interview_scheduled', 'scheduled_interview', 'hen_phong_van', 'phong_van'],

            'docs_preparing' => [
                'docs_preparing',
                'prepare_documents',
                'preparing_documents',
                'document_preparing',
                'dang_lam_ho_so',
                'chuan_bi_ho_so',
                'pass',
                'interview_passed',
            ],

            'docs_done' => ['docs_done', 'done_documents', 'hoan_tat_ho_so', 'hoan_thanh_ho_so'],
            'coe_waiting' => ['coe_waiting', 'waiting_coe', 'wait_coe', 'cho_coe', 'doi_coe', 'cho_ket_qua_coe'],
            'has_coe' => ['has_coe', 'got_coe', 'coe_done', 'coe_received', 'coe_has', 'da_co_coe'],
            'visa_processing' => ['visa_processing', 'doing_visa', 'lam_visa', 'dang_lam_visa'],
            'ticket_booking' => ['ticket_booking', 'book_ticket', 'buy_ticket', 'mua_ve', 'mua_ve_nhap_canh'],
            'pre_departure' => ['pre_departure', 'before_departure', 'departure_waiting', 'prepare_flight', 'chuan_bi_bay', 'cho_xuat_canh'],
            'entry' => ['entry', 'entered', 'da_nhap_canh'],
            'in_japan' => ['in_japan', 'dang_o_nhat'],
            'returned' => ['returned', 'return', 'pre_return', 'returned_vn', 'back_vn', 've_nuoc', 'da_ve_nuoc'],
            'cancelled' => ['cancelled', 'canceled', 'huy', 'huỷ', 'da_huy', 'da_huỷ', 'stopped', 'stop', 'dung_ho_so', 'fail', 'interview_fail', 'rejected'],
        ];

        return $map[$progress] ?? [$progress];
    }
}

if (!function_exists('im_dossier_progress_ui_map')) {
    function im_dossier_progress_ui_map()
    {
        $out = [];

        foreach (array_keys(im_dossier_progress_list()) as $canonical) {
            foreach (im_dossier_progress_aliases($canonical) as $alias) {
                if ($alias === null) {
                    continue;
                }

                $alias = (string)$alias;
                $out[$alias] = $canonical;
            }

            $out[$canonical] = $canonical;
        }

        $out[''] = 'not_updated';

        return $out;
    }
}

if (!function_exists('im_application_filter_target')) {
    function im_application_filter_target($value)
    {
        $iv = im_normalize_interview_result($value);
        if ($iv === 'pass') {
            return [
                'type'   => 'interview_result',
                'values' => ['pass', 'passed', 'dat', 'dau', 'interview_passed', 'passed_interview', 'dau_phong_van'],
            ];
        }

        if ($iv === 'fail') {
            return [
                'type'   => 'interview_result',
                'values' => ['fail', 'failed', 'rot', 'interview_fail', 'interview_failed', 'rot_phong_van', 'rejected'],
            ];
        }

        $progress = im_normalize_dossier_progress($value);

        return [
            'type'   => 'dossier_progress',
            'values' => im_dossier_progress_aliases($progress),
        ];
    }
}

if (!function_exists('im_progress_implies_interview')) {
    function im_progress_implies_interview($progress)
    {
        $progress = im_normalize_dossier_progress($progress);

        if (in_array($progress, [
            'docs_preparing',
            'docs_done',
            'coe_waiting',
            'has_coe',
            'visa_processing',
            'ticket_booking',
            'pre_departure',
            'entry',
            'in_japan',
            'returned',
        ], true)) {
            return 'pass';
        }

        if ($progress === 'cancelled') {
            return 'fail';
        }

        return '';
    }
}

if (!function_exists('im_sync_from_dossier_progress')) {
    function im_sync_from_dossier_progress($progress)
    {
        $progress = im_normalize_dossier_progress($progress);
        $interview = im_progress_implies_interview($progress);

        return [$progress, $interview];
    }
}

if (!function_exists('im_sync_from_interview_result')) {
    function im_sync_from_interview_result($interview, $current_progress = '')
    {
        $interview = im_normalize_interview_result($interview);
        $current = im_normalize_dossier_progress($current_progress);

        $rank = [
            'not_updated'         => 0,
            'applied'             => 1,
            'interview_scheduled' => 2,
            'docs_preparing'      => 3,
            'docs_done'           => 4,
            'coe_waiting'         => 5,
            'has_coe'             => 6,
            'visa_processing'     => 7,
            'ticket_booking'      => 8,
            'pre_departure'       => 9,
            'entry'               => 10,
            'in_japan'            => 11,
            'returned'            => 12,
            'cancelled'           => 13,
        ];

        if ($interview === 'pass') {
            if (($rank[$current] ?? 0) < $rank['docs_preparing'] || $current === 'cancelled') {
                return ['docs_preparing', 'pass'];
            }

            return [$current, 'pass'];
        }

        if ($interview === 'fail') {
            return ['cancelled', 'fail'];
        }

        if (im_progress_implies_interview($current) === 'pass') {
            return [$current, 'pass'];
        }

        if (im_progress_implies_interview($current) === 'fail') {
            return [$current, 'fail'];
        }

        return ['applied', ''];
    }
}

if (!function_exists('im_status_rank')) {
    function im_status_rank($type, $status)
    {
        $status = im_normalize_status($status);
        $order = im_status_order($type);
        $idx = array_search($status, $order, true);
        return $idx === false ? 0 : (int)$idx;
    }
}

if (!function_exists('im_manage_bucket_from_status')) {
    function im_manage_bucket_from_status($status)
    {
        $status = im_normalize_status($status);

        if ($status === 'cancelled') {
            return 'cancelled';
        }

        if ($status === 'returned') {
            return 'returned';
        }

        if (in_array($status, ['entry', 'in_japan'], true)) {
            return 'in_japan';
        }

        return 'processing';
    }
}

if (!function_exists('im_status_label_jp')) {
    function im_status_label_jp($status)
    {
        $iv = im_normalize_interview_result($status);

        if ($iv === 'pass') {
            return '面接合格';
        }

        if ($iv === 'fail') {
            return '面接不合格';
        }

        $status = im_normalize_dossier_progress($status);

        $labels = [
            'not_updated'         => '未更新',
            'applied'             => '応募済み',
            'interview_scheduled' => '面接予定',
            'docs_preparing'      => '書類準備中',
            'docs_done'           => '書類完了',
            'coe_waiting'         => 'COE待ち',
            'has_coe'             => 'COE交付済み',
            'visa_processing'     => 'ビザ申請中',
            'ticket_booking'      => '航空券手配',
            'pre_departure'       => '出国準備',
            'entry'               => '入国済み',
            'in_japan'            => '来日済み',
            'returned'            => '帰国済み',
            'cancelled'           => 'キャンセル',
        ];

        return $labels[$status] ?? (string)$status;
    }
}
if (!function_exists('im_calendar_event_type_key')) {
    function im_calendar_event_type_key($type)
    {
        $key = im_status_clean_key($type);

        $map = [
            'interview' => 'interview',
            'pv'        => 'interview',
            'phong_van' => 'interview',

            'entry'     => 'entry',
            'nhap_canh' => 'entry',

            'return'    => 'return',
            'returned'  => 'return',
            've_nuoc'   => 'return',
        ];

        return $map[$key] ?? 'entry';
    }
}

if (!function_exists('im_calendar_event_type_label')) {
    function im_calendar_event_type_label($type)
    {
        $key = im_calendar_event_type_key($type);

        $labels = [
            'interview' => 'Phỏng vấn',
            'entry'     => 'Nhập cảnh',
            'return'    => 'Về nước',
        ];

        return $labels[$key] ?? 'Khác';
    }
}

if (!function_exists('im_calendar_event_type_color')) {
    function im_calendar_event_type_color($type)
    {
        $key = im_calendar_event_type_key($type);

        $colors = [
            'interview' => '#2563eb',
            'entry'     => '#10b981',
            'return'    => '#ef4444',
        ];

        return $colors[$key] ?? '#64748b';
    }
}

if (!function_exists('im_calendar_event_type_badge_class')) {
    function im_calendar_event_type_badge_class($type)
    {
        $key = im_calendar_event_type_key($type);

        if ($key === 'interview') {
            return 'pv';
        }

        return $key;
    }
}

if (!function_exists('im_calendar_status_key')) {
    function im_calendar_status_key($status)
    {
        $raw = trim((string)$status);

        if ($raw === '') {
            return 'not_updated';
        }

        $progressKey = function_exists('im_normalize_dossier_progress')
            ? im_normalize_dossier_progress($raw)
            : 'not_updated';

        if ($progressKey !== 'not_updated') {
            return $progressKey;
        }

        $statusKey = function_exists('im_normalize_status')
            ? im_normalize_status($raw)
            : 'not_updated';

        if ($statusKey !== '' && $statusKey !== 'not_updated') {
            return $statusKey;
        }

        return 'not_updated';
    }
}

if (!function_exists('im_calendar_status_label')) {
    function im_calendar_status_label($status)
    {
        $key = im_calendar_status_key($status);

        $progressList = function_exists('im_dossier_progress_list')
            ? im_dossier_progress_list()
            : [];

        if (isset($progressList[$key])) {
            return $progressList[$key];
        }

        if (function_exists('im_status_label_vi')) {
            return im_status_label_vi($key);
        }

        return $key !== '' ? $key : 'Chưa cập nhật';
    }
}

if (!function_exists('im_calendar_status_class')) {
    function im_calendar_status_class($status)
    {
        $key = im_calendar_status_key($status);

        $classes = [
            'not_updated'         => 'slate',
            'applied'             => 'blue',
            'interview_scheduled' => 'blue',

            'pass'                => 'green',
            'fail'                => 'red',

            'docs_preparing'      => 'amber',
            'docs_done'           => 'green',
            'coe_waiting'         => 'blue',
            'has_coe'             => 'green',
            'visa_processing'     => 'cyan',
            'ticket_booking'      => 'amber',
            'pre_departure'       => 'amber',

            'entry'               => 'green',
            'in_japan'            => 'green',
            'returned'            => 'green',
            'cancelled'           => 'red',
        ];

        return $classes[$key] ?? 'slate';
    }
}

if (!function_exists('im_calendar_is_cancelled_row')) {
    function im_calendar_is_cancelled_row(array $row)
    {
        $values = [
            $row['dossier_progress'] ?? '',
            $row['status'] ?? '',
            $row['status_label'] ?? '',
            $row['interview_result'] ?? '',
        ];

        foreach ($values as $value) {
            $value = trim((string)$value);

            if ($value === '') {
                continue;
            }

            if (function_exists('im_normalize_dossier_progress') && im_normalize_dossier_progress($value) === 'cancelled') {
                return true;
            }

            if (function_exists('im_normalize_status') && im_normalize_status($value) === 'cancelled') {
                return true;
            }

            if (function_exists('im_normalize_interview_result') && im_normalize_interview_result($value) === 'fail') {
                return true;
            }

            $lower = mb_strtolower($value);

            if (in_array($lower, ['đã hủy', 'đã huỷ', 'hủy', 'huỷ', 'rớt', 'trượt'], true)) {
                return true;
            }
        }

        return false;
    }
}