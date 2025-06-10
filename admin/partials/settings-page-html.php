<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
        <?php
        // Output security fields for the registered setting group
        settings_fields('invoice_management_options_group');
        // Output setting sections and their fields
        do_settings_sections('invoice-management-settings');
        // Output save settings button
        submit_button(__('Save Settings', 'invoice-management'));
        ?>
    </form>
</div>
