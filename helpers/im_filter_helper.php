<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('im_month_options')) {
    function im_month_options($include_all = true)
    {
        $months = [];

        if ($include_all) {
            $months[] = [
                'value' => 0,
                'label' => 'Tất cả',
            ];
        }

        for ($m = 1; $m <= 12; $m++) {
            $months[] = [
                'value' => $m,
                'label' => 'Tháng ' . $m,
            ];
        }

        return $months;
    }
}