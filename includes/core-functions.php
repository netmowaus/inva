<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Retrieves company details saved in the Invoice Settings.
 *
 * @since 1.0.0
 *
 * @param string|null $key Optional. The specific detail to retrieve (e.g., 'company_name').
 *                         If null, returns all company details as an array.
 * @param mixed $default Optional. Default value to return if the key is not found. Only used if $key is specified.
 * @return mixed|array|null The requested company detail, all details as an array, or null/default if not found.
 */
function get_invoice_company_details($key = null, $default = null) {
    $company_details = get_option('invoice_management_company_details');

    if (empty($company_details) || !is_array($company_details)) {
        return $key ? $default : array(); // Return empty array if no settings or not an array
    }

    if (null !== $key) {
        return isset($company_details[$key]) ? $company_details[$key] : $default;
    }

    return $company_details;
}
