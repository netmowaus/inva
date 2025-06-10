<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Invoice_Management_CPTs')) {

    class Invoice_Management_CPTs {

        private static $instance;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // CPT Registrations
            add_action('init', array($this, 'register_client_cpt'));
            add_action('init', array($this, 'register_quote_cpt'));

            // Meta Boxes for Client
            add_action('add_meta_boxes', array($this, 'add_client_meta_boxes'));
            add_action('save_post_invoice_client', array($this, 'save_client_meta_fields'));

            // Custom Admin Columns for Client CPT
            add_filter('manage_invoice_client_posts_columns', array($this, 'set_custom_edit_invoice_client_columns'));
            add_action('manage_invoice_client_posts_custom_column', array($this, 'custom_invoice_client_column_content'), 10, 2);
            add_filter('manage_edit-invoice_client_sortable_columns', array($this, 'make_invoice_client_columns_sortable'));

            // Meta Boxes for Quote
            add_action('add_meta_boxes', array($this, 'add_quote_meta_boxes'));
            add_action('save_post_invoice_quote', array($this, 'save_quote_meta_fields'));
        }

        // ---------------------------------------------------------------------
        // Client CPT and Meta (Full definitions restored)
        // ---------------------------------------------------------------------
        public function register_client_cpt() {
            $labels = array(
                'name'                  => _x('Clients', 'Post type general name', 'invoice-management'),
                'singular_name'         => _x('Client', 'Post type singular name', 'invoice-management'),
                'menu_name'             => _x('Clients', 'Admin Menu text', 'invoice-management'),
                'name_admin_bar'        => _x('Client', 'Add New on Toolbar', 'invoice-management'),
                'add_new'               => __('Add New', 'invoice-management'),
                'add_new_item'          => __('Add New Client', 'invoice-management'),
                'new_item'              => __('New Client', 'invoice-management'),
                'edit_item'             => __('Edit Client', 'invoice-management'),
                'view_item'             => __('View Client', 'invoice-management'),
                'all_items'             => __('All Clients', 'invoice-management'),
                'search_items'          => __('Search Clients', 'invoice-management'),
                'parent_item_colon'     => __('Parent Clients:', 'invoice-management'),
                'not_found'             => __('No clients found.', 'invoice-management'),
                'not_found_in_trash'    => __('No clients found in Trash.', 'invoice-management'),
                'featured_image'        => _x('Client Profile Image', 'Overrides the \'Featured Image\' phrase for this post type.', 'invoice-management'),
                'set_featured_image'    => _x('Set profile image', 'Overrides the \'Set featured image\' phrase for this post type.', 'invoice-management'),
                'remove_featured_image' => _x('Remove profile image', 'Overrides the \'Remove featured image\' phrase for this post type.', 'invoice-management'),
                'use_featured_image'    => _x('Use as profile image', 'Overrides the \'Use as featured image\' phrase for this post type.', 'invoice-management'),
                'archives'              => _x('Client archives', 'The post type archive label used in nav menus.', 'invoice-management'),
                'insert_into_item'      => _x('Insert into client', 'Overrides the \'Insert into post\'/\'Insert into page\' phrase (used when inserting media into a post).', 'invoice-management'),
                'uploaded_to_this_item' => _x('Uploaded to this client', 'Overrides the \'Uploaded to this post\'/\'Uploaded to this page\' phrase (used when viewing media attached to a post).', 'invoice-management'),
                'filter_items_list'     => _x('Filter clients list', 'Screen reader text for the filter links heading on the post type listing screen.', 'invoice-management'),
                'items_list_navigation' => _x('Clients list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'invoice-management'),
                'items_list'            => _x('Clients list', 'Screen reader text for the items list heading on the post type listing screen.', 'invoice-management'),
            );
            $args = array(
                'labels'             => $labels,
                'public'             => true, 'publicly_queryable' => true, 'show_ui' => true, 'show_in_menu' => true,
                'query_var'          => true, 'rewrite' => array('slug' => 'client'), 'capability_type' => 'post',
                'has_archive'        => true, 'hierarchical' => false, 'menu_position' => 20,
                'supports'           => array('title', 'editor', 'custom-fields', 'thumbnail'),
                'menu_icon'          => 'dashicons-businessman', 'show_in_rest' => true,
            );
            register_post_type('invoice_client', $args);
        }

        public function add_client_meta_boxes() {
             add_meta_box(
                'invoice_client_details_meta_box',
                __('Client Details', 'invoice-management'),
                array($this, 'render_client_details_meta_box_html'),
                'invoice_client',
                'normal',
                'high'
            );
        }

        public function render_client_details_meta_box_html($post) {
            wp_nonce_field('invoice_client_details_save_action', 'invoice_client_details_nonce');
            $client_email = get_post_meta($post->ID, '_client_email', true);
            $client_phone = get_post_meta($post->ID, '_client_phone', true);
            $client_address_street = get_post_meta($post->ID, '_client_address_street', true);
            $client_address_city = get_post_meta($post->ID, '_client_address_city', true);
            $client_address_state = get_post_meta($post->ID, '_client_address_state', true);
            $client_address_zip = get_post_meta($post->ID, '_client_address_zip', true);
            $client_address_country = get_post_meta($post->ID, '_client_address_country', true);
            ?>
            <table class="form-table">
                <tr><th><label for="client_email"><?php esc_html_e('Email Address', 'invoice-management'); ?></label></th>
                    <td><input type="email" id="client_email" name="client_email" value="<?php echo esc_attr($client_email); ?>" class="regular-text" /></td></tr>
                <tr><th><label for="client_phone"><?php esc_html_e('Phone Number', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="client_phone" name="client_phone" value="<?php echo esc_attr($client_phone); ?>" class="regular-text" /></td></tr>
                <tr><th><label for="client_address_street"><?php esc_html_e('Street Address', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="client_address_street" name="client_address_street" value="<?php echo esc_attr($client_address_street); ?>" class="regular-text" /></td></tr>
                <tr><th><label for="client_address_city"><?php esc_html_e('City', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="client_address_city" name="client_address_city" value="<?php echo esc_attr($client_address_city); ?>" class="regular-text" /></td></tr>
                <tr><th><label for="client_address_state"><?php esc_html_e('State/Province', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="client_address_state" name="client_address_state" value="<?php echo esc_attr($client_address_state); ?>" class="regular-text" /></td></tr>
                <tr><th><label for="client_address_zip"><?php esc_html_e('ZIP/Postal Code', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="client_address_zip" name="client_address_zip" value="<?php echo esc_attr($client_address_zip); ?>" class="regular-text" /></td></tr>
                <tr><th><label for="client_address_country"><?php esc_html_e('Country', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="client_address_country" name="client_address_country" value="<?php echo esc_attr($client_address_country); ?>" class="regular-text" /></td></tr>
            </table>
            <?php
        }

        public function save_client_meta_fields($post_id) {
            if (!isset($_POST['invoice_client_details_nonce']) || !wp_verify_nonce($_POST['invoice_client_details_nonce'], 'invoice_client_details_save_action')) return;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (!current_user_can('edit_post', $post_id)) return;

            $meta_fields = array(
                '_client_email'          => 'sanitize_email',
                '_client_phone'          => 'sanitize_text_field',
                '_client_address_street' => 'sanitize_text_field',
                '_client_address_city'   => 'sanitize_text_field',
                '_client_address_state'  => 'sanitize_text_field',
                '_client_address_zip'    => 'sanitize_text_field',
                '_client_address_country'=> 'sanitize_text_field',
            );

            foreach ($meta_fields as $meta_key => $sanitize_callback) {
                $post_field_name = str_replace('_client_', 'client_', $meta_key); // Example mapping
                if (isset($_POST[$post_field_name])) {
                    $value = call_user_func($sanitize_callback, $_POST[$post_field_name]);
                    if (!empty($value)) {
                        update_post_meta($post_id, $meta_key, $value);
                    } else {
                        delete_post_meta($post_id, $meta_key);
                    }
                }
            }
        }

        public function set_custom_edit_invoice_client_columns($columns) {
            $new_columns = array('cb' => $columns['cb'], 'title' => $columns['title'],
                'client_email' => __('Email', 'invoice-management'), 'client_phone' => __('Phone', 'invoice-management'));
            if (isset($columns['date'])) {
                $new_columns['date'] = $columns['date'];
            }
            return $new_columns;
        }
        public function custom_invoice_client_column_content($column, $post_id) {
            switch ($column) {
                case 'client_email': echo esc_html(get_post_meta($post_id, '_client_email', true)); break;
                case 'client_phone': echo esc_html(get_post_meta($post_id, '_client_phone', true)); break;
            }
        }
        public function make_invoice_client_columns_sortable($sortable_columns) {
            $sortable_columns['client_email'] = '_client_email'; return $sortable_columns;
        }

        // ---------------------------------------------------------------------
        // Quote CPT and Meta
        // ---------------------------------------------------------------------
        public function register_quote_cpt() {
            $labels = array(
                'name'                  => _x('Quotes', 'Post type general name', 'invoice-management'),
                'singular_name'         => _x('Quote', 'Post type singular name', 'invoice-management'),
                'menu_name'             => _x('Quotes', 'Admin Menu text', 'invoice-management'),
                'name_admin_bar'        => _x('Quote', 'Add New on Toolbar', 'invoice-management'),
                'add_new'               => __('Add New', 'invoice-management'),
                'add_new_item'          => __('Add New Quote', 'invoice-management'),
                'new_item'              => __('New Quote', 'invoice-management'),
                'edit_item'             => __('Edit Quote', 'invoice-management'),
                'view_item'             => __('View Quote', 'invoice-management'),
                'all_items'             => __('All Quotes', 'invoice-management'),
                'search_items'          => __('Search Quotes', 'invoice-management'),
                'parent_item_colon'     => __('Parent Quotes:', 'invoice-management'),
                'not_found'             => __('No quotes found.', 'invoice-management'),
                'not_found_in_trash'    => __('No quotes found in Trash.', 'invoice-management'),
                'archives'              => _x('Quote archives', 'The post type archive label used in nav menus.', 'invoice-management'),
                'insert_into_item'      => _x('Insert into quote', 'Overrides the \'Insert into post\'/\'Insert into page\' phrase (used when inserting media into a post).', 'invoice-management'),
                'uploaded_to_this_item' => _x('Uploaded to this quote', 'Overrides the \'Uploaded to this post\'/\'Uploaded to this page\' phrase (used when viewing media attached to a post).', 'invoice-management'),
                'filter_items_list'     => _x('Filter quotes list', 'Screen reader text for the filter links heading on the post type listing screen.', 'invoice-management'),
                'items_list_navigation' => _x('Quotes list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'invoice-management'),
                'items_list'            => _x('Quotes list', 'Screen reader text for the items list heading on the post type listing screen.', 'invoice-management'),
            );
            $args = array(
                'labels'             => $labels,
                'public'             => true, 'publicly_queryable' => true, 'show_ui' => true, 'show_in_menu' => true,
                'query_var'          => true, 'rewrite' => array('slug' => 'quote'), 'capability_type' => 'post',
                'has_archive'        => true, 'hierarchical' => false, 'menu_position' => 21,
                'supports'           => array('title', 'editor', 'custom-fields'),
                'menu_icon'          => 'dashicons-format-aside', 'show_in_rest' => true,
            );
            register_post_type('invoice_quote', $args);
        }

        public function add_quote_meta_boxes() {
            add_meta_box(
                'invoice_quote_details_meta_box',
                __('Quote Details', 'invoice-management'),
                array($this, 'render_quote_details_meta_box_html'),
                'invoice_quote',
                'normal', 'high'
            );
        }

        public function render_quote_details_meta_box_html($post) {
            wp_nonce_field('invoice_quote_details_save_action', 'invoice_quote_details_nonce');

            $quote_client_id = get_post_meta($post->ID, '_quote_client_id', true);
            $quote_number = get_post_meta($post->ID, '_quote_number', true);
            $quote_date = get_post_meta($post->ID, '_quote_date', true);
            $quote_expiry_date = get_post_meta($post->ID, '_quote_expiry_date', true);
            $quote_status = get_post_meta($post->ID, '_quote_status', true);
            $quote_notes = get_post_meta($post->ID, '_quote_notes', true);

            $clients_query = new WP_Query(array(
                'post_type' => 'invoice_client', 'posts_per_page' => -1,
                'orderby' => 'title', 'order' => 'ASC'
            ));
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="quote_client_id"><?php esc_html_e('Client', 'invoice-management'); ?></label></th>
                    <td>
                        <select id="quote_client_id" name="quote_client_id" class="regular-text">
                            <option value=""><?php esc_html_e('-- Select Client --', 'invoice-management'); ?></option>
                            <?php if ($clients_query->have_posts()) : ?>
                                <?php while ($clients_query->have_posts()) : $clients_query->the_post(); ?>
                                    <option value="<?php echo esc_attr(get_the_ID()); ?>" <?php selected($quote_client_id, get_the_ID()); ?>>
                                        <?php echo esc_html(get_the_title()); ?>
                                    </option>
                                <?php endwhile; wp_reset_postdata(); ?>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="quote_number"><?php esc_html_e('Quote Number', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="quote_number" name="quote_number" value="<?php echo esc_attr($quote_number); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="quote_date"><?php esc_html_e('Quote Date', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="quote_date" name="quote_date" value="<?php echo esc_attr($quote_date); ?>" class="regular-text datepicker" placeholder="YYYY-MM-DD" /></td>
                </tr>
                <tr>
                    <th><label for="quote_expiry_date"><?php esc_html_e('Expiry Date', 'invoice-management'); ?></label></th>
                    <td><input type="text" id="quote_expiry_date" name="quote_expiry_date" value="<?php echo esc_attr($quote_expiry_date); ?>" class="regular-text datepicker" placeholder="YYYY-MM-DD" /></td>
                </tr>
                <tr>
                    <th><label for="quote_status"><?php esc_html_e('Status', 'invoice-management'); ?></label></th>
                    <td>
                        <select id="quote_status" name="quote_status" class="regular-text">
                            <?php
                            $statuses = array(
                                'pending' => __('Pending', 'invoice-management'), 'sent' => __('Sent', 'invoice-management'),
                                'accepted' => __('Accepted', 'invoice-management'), 'rejected' => __('Rejected', 'invoice-management'),
                                'invoiced' => __('Invoiced', 'invoice-management'),
                            );
                            foreach ($statuses as $key => $label) {
                                echo '<option value="' . esc_attr($key) . '" ' . selected($quote_status, $key, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="quote_notes"><?php esc_html_e('Notes/Terms', 'invoice-management'); ?></label></th>
                    <td><textarea id="quote_notes" name="quote_notes" rows="5" class="large-text"><?php echo esc_textarea($quote_notes); ?></textarea></td>
                </tr>
            </table>
            <?php
        }

        public function save_quote_meta_fields($post_id) {
            if (!isset($_POST['invoice_quote_details_nonce']) || !wp_verify_nonce($_POST['invoice_quote_details_nonce'], 'invoice_quote_details_save_action')) return;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (!current_user_can('edit_post', $post_id)) return;

            $meta_fields = array(
                '_quote_client_id'    => 'absint',
                '_quote_number'       => 'sanitize_text_field',
                '_quote_date'         => 'sanitize_text_field', // Ideally, validate as date
                '_quote_expiry_date'  => 'sanitize_text_field', // Ideally, validate as date
                '_quote_status'       => 'sanitize_key',
                '_quote_notes'        => 'sanitize_textarea_field',
            );

            foreach ($meta_fields as $meta_key => $sanitize_callback) {
                $post_field_name = str_replace('_quote_', 'quote_', $meta_key); // e.g. _quote_client_id -> quote_client_id
                 if (isset($_POST[$post_field_name])) {
                    $value = call_user_func($sanitize_callback, $_POST[$post_field_name]);
                    if ($value !== '' && $value !== null && !($meta_key === '_quote_client_id' && $value === 0 && empty($_POST[$post_field_name])) ) { // Allow 0 for client ID if it's explicitly set, but not if empty string was passed
                        update_post_meta($post_id, $meta_key, $value);
                    } else {
                        delete_post_meta($post_id, $meta_key);
                    }
                } else {
                     // If a field isn't set in POST (e.g. a checkbox that's unchecked), you might want to delete its meta.
                     // For text fields and dropdowns, they are usually present, just possibly empty.
                     // This part depends on specific field types not covered here (e.g. checkboxes)
                     // For the current fields, if not set, it implies it should be deleted or not set.
                     delete_post_meta($post_id, $meta_key);
                }
            }
        }
    } // End class Invoice_Management_CPTs
}
