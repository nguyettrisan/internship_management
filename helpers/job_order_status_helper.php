<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('im_job_order_normalize_status')) {
    function im_job_order_normalize_status($status)
    {
        if (is_numeric($status)) {
            $num = (int)$status;
            $legacyNumMap = [
                0 => 'received',
                1 => 'sent_to_schools',
                2 => 'has_applicants',
                3 => 'interview_scheduled',
                4 => 'interview_result',
                5 => 'making_documents',
                6 => 'waiting_coe',
                7 => 'coe_done',
            ];
            return $legacyNumMap[$num] ?? 'received';
        }

        $status = trim((string)$status);
        if ($status === '') {
            return 'received';
        }

        $status = function_exists('mb_strtolower')
            ? mb_strtolower($status, 'UTF-8')
            : strtolower($status);

        $status = str_replace([' ', '-'], '_', $status);
        $status = preg_replace('/_+/', '_', $status);

        /*$aliases = [
            'sent_schools'      => 'sent_to_schools',
            'sent_to_schools'   => 'sent_to_schools',
            'sent_school'       => 'sent_to_schools',
            //'has_students'      => 'has_applicants',
            'recruiting'        => 'recruiting',
            'has_applicants'    => 'recruiting',
            'has_students'      => 'recruiting',
            'interview_done'    => 'interview_result',
            'done_documents'    => 'docs_done',
            'coe_waiting'       => 'waiting_coe',
            'got_coe'           => 'coe_done',
            'entered'           => 'entry',
            'canceled'          => 'cancelled',
            'docs_preparing'    => 'making_documents',
            'prepare_documents' => 'making_documents',
            'processing'        => 'making_documents',
            'approved'          => 'docs_done',
            'rejected'          => 'cancelled',
            'closed'            => 'done',
            'interviewing'      => 'interview_scheduled',
            'interviewed'       => 'interview_result',
        ];*/
        
        $aliases = [
            // job order status cũ
            'sent_schools'      => 'sent_to_schools',
            'sent_to_schools'   => 'sent_to_schools',
            'sent_school'       => 'sent_to_schools',

            'recruiting'        => 'recruiting',
            'has_applicants'    => 'recruiting',
            'has_students'      => 'recruiting',

            'interview_done'    => 'interview_result',
            'interviewing'      => 'interview_scheduled',
            'interviewed'       => 'interview_result',

            'done_documents'    => 'docs_done',
            'coe_waiting'       => 'waiting_coe',
            'got_coe'           => 'coe_done',
            'entered'           => 'entry',
            'canceled'          => 'cancelled',
            'docs_preparing'    => 'making_documents',
            'prepare_documents' => 'making_documents',
            'processing'        => 'making_documents',
            'approved'          => 'docs_done',
            'rejected'          => 'cancelled',
            'closed'            => 'done',

            // applicant / pipeline status mới thêm
            'not_updated'       => 'not_updated',
            'chua_cap_nhat'     => 'not_updated',
            'notupdated'        => 'not_updated',

            'applied'           => 'applied',
            'ung_tuyen'         => 'applied',
            'submitted'         => 'applied',

            'pass'              => 'pass',
            'passed'            => 'pass',
            'dat'               => 'pass',

            'fail'              => 'fail',
            'rot'               => 'fail',

            'prepare_documents' => 'making_documents',
            'making_documents'  => 'making_documents',
            'docs_preparing'    => 'making_documents',

            'docs_done'         => 'docs_done',
            'done_documents'    => 'docs_done',

            'waiting_coe'       => 'waiting_coe',
            'coe_waiting'       => 'waiting_coe',

            'coe_done'          => 'coe_done',
            'got_coe'           => 'coe_done',
            'has_coe'           => 'has_coe',
            'coe_received'      => 'has_coe',

            'visa_processing'   => 'visa_processing',
            'doing_visa'        => 'visa_processing',

            'ticket_booking'    => 'ticket_booking',
            'buy_ticket'        => 'ticket_booking',

            'pre_departure'     => 'pre_departure',
            'prepare_flight'    => 'pre_departure',

            'entry'             => 'entry',
            'entered'           => 'entry',

            'in_japan'          => 'in_japan',
            'returned'          => 'returned',
            'da_ve_nuoc'        => 'returned',

            'stopped'           => 'stopped',
            'stop'              => 'stopped',
            'dung_ho_so'        => 'stopped',

            'cancelled'         => 'cancelled',
            'canceled'          => 'cancelled',
            'huy'               => 'cancelled',
        ];

        return $aliases[$status] ?? $status;
    }
}

if (!function_exists('im_job_order_status_map')) {
    function im_job_order_status_map()
    {
        return [
            'received' => [
                'vi'    => 'Tiếp nhận đơn',
                'jp'    => '受付済み（求人票受領）',
                'color' => 'primary',
            ],
            'sent_to_schools' => [
                'vi'    => 'Đã gửi đến trường',
                'jp'    => '提携校へ求人紹介済み',
                'color' => 'info',
            ],
            'has_applicants' => [
                'vi'    => 'Đã có ứng viên',
                'jp'    => '学生応募あり',
                'color' => 'info',
            ],
            'recruiting' => [
                'vi'    => 'Đang tuyển ứng viên',
                'jp'    => '募集中',
                'color' => 'info',
            ],
            'interview_scheduled' => [
                'vi'    => 'Đã lên lịch phỏng vấn',
                'jp'    => '面接予定',
                'color' => 'warning',
            ],
            'interview_result' => [
                'vi'    => 'Đã phỏng vấn – chờ kết quả',
                'jp'    => '面接済み・結果待ち',
                'color' => 'warning',
            ],
            'making_documents' => [
                'vi'    => 'Đang làm hồ sơ',
                'jp'    => '書類作成中',
                'color' => 'primary',
            ],
            'docs_done' => [
                'vi'    => 'Đã hoàn tất hồ sơ',
                'jp'    => '書類完了',
                'color' => 'success',
            ],
            'waiting_coe' => [
                'vi'    => 'Chờ kết quả COE',
                'jp'    => 'COE結果待ち',
                'color' => 'default',
            ],
            'coe_done' => [
                'vi'    => 'Đã có COE – chờ nhập cảnh',
                'jp'    => 'COE交付済み・入国待ち',
                'color' => 'success',
            ],
            'entry' => [
                'vi'    => 'Đã nhập cảnh',
                'jp'    => '入国済み',
                'color' => 'success',
            ],
            'done' => [
                'vi'    => 'Đã hoàn tất chương trình',
                'jp'    => '完了',
                'color' => 'default',
            ],
            'cancelled' => [
                'vi'    => 'Đã hủy',
                'jp'    => 'キャンセル',
                'color' => 'default',
            ],
            // pipeline / applicant statuses thêm mới
            'not_updated' => [
                'vi'    => 'Chưa cập nhật',
                'jp'    => '未更新',
                'color' => 'default',
            ],
            'applied' => [
                'vi'    => 'Ứng tuyển',
                'jp'    => '応募済み',
                'color' => 'info',
            ],
            'pass' => [
                'vi'    => 'Đạt',
                'jp'    => '合格',
                'color' => 'success',
            ],
            'fail' => [
                'vi'    => 'Rớt',
                'jp'    => '不合格',
                'color' => 'danger',
            ],
            'has_coe' => [
                'vi'    => 'Đã có COE',
                'jp'    => 'COE取得済み',
                'color' => 'success',
            ],
            'visa_processing' => [
                'vi'    => 'Đang làm visa',
                'jp'    => 'ビザ手続き中',
                'color' => 'warning',
            ],
            'ticket_booking' => [
                'vi'    => 'Mua vé nhập cảnh',
                'jp'    => '航空券手配中',
                'color' => 'warning',
            ],
            'pre_departure' => [
                'vi'    => 'Chuẩn bị bay',
                'jp'    => '渡日前準備',
                'color' => 'warning',
            ],
            'in_japan' => [
                'vi'    => 'Đang ở Nhật',
                'jp'    => '日本滞在中',
                'color' => 'success',
            ],
            'returned' => [
                'vi'    => 'Đã về nước',
                'jp'    => '帰国済み',
                'color' => 'default',
            ],
            'stopped' => [
                'vi'    => 'Dừng hồ sơ',
                'jp'    => '手続き停止',
                'color' => 'danger',
            ],
        ];
    }
}

if (!function_exists('im_job_order_status_meta')) {
    function im_job_order_status_meta($status)
    {
        $raw = trim((string)$status);
        $key = im_job_order_normalize_status($status);
        $map = im_job_order_status_map();

        if (isset($map[$key])) {
            return [
                'key'   => $key,
                'raw'   => $raw,
                'vi'    => $map[$key]['vi'],
                'jp'    => $map[$key]['jp'],
                'color' => $map[$key]['color'],
            ];
        }

        return [
            'key'   => $key,
            'raw'   => $raw,
            'vi'    => ($raw !== '' ? $raw : 'Tiếp nhận đơn'),
            'jp'    => ($raw !== '' ? $raw : '受付済み'),
            'color' => 'default',
        ];
    }
}

if (!function_exists('im_job_order_status_label')) {
    function im_job_order_status_label($status, $lang = 'vi')
    {
        $meta = im_job_order_status_meta($status);
        return ($lang === 'jp') ? $meta['jp'] : $meta['vi'];
    }
}

if (!function_exists('im_job_order_status_color')) {
    function im_job_order_status_color($status)
    {
        $meta = im_job_order_status_meta($status);
        return $meta['color'];
    }
}