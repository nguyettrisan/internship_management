<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hook['app_custom_routes'][] = function($route) {

    // =====================
    // PUBLIC INVOICE ROUTES
    // =====================
    $route->add('invoice/(:any)', 'internship_management/internship_invoice_public/view/$1');
    $route->add('invoice-id/(:num)', 'internship_management/internship_invoice_public/view_online/$1');

    // =====================
    // PUBLIC SURVEY FORM
    // =====================
    $route->add('survey_form/(:num)/(:num)', 'internship_management/survey_form/index/$1/$2');
    $route->add('survey_form/index/(:num)/(:num)', 'internship_management/survey_form/index/$1/$2');
};