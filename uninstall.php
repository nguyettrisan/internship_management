<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Perfex uninstall hook entrypoint.
 * By default we do NOT drop data to avoid loss.
 */
function internship_management_uninstall_run()
{
    // Intentionally left blank.

    // If you want to drop tables, you can move the DROP TABLE statements
    // below into this function and uncomment them.
}

// By default we do not drop tables to avoid data loss.
// If you want to drop tables, uncomment below.

/*
$CI = &get_instance();
$CI->db->query('DROP TABLE IF EXISTS `'.db_prefix().'internship_job_order_applicants`');
$CI->db->query('DROP TABLE IF EXISTS `'.db_prefix().'internship_job_orders`');
$CI->db->query('DROP TABLE IF EXISTS `'.db_prefix().'internship_applications`');
$CI->db->query('DROP TABLE IF EXISTS `'.db_prefix().'internship_audit_logs`');
*/

