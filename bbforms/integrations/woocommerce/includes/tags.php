<?php
/**
 * Tags
 *
 * @package     BBForms\WooCommerce\Tags
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register new tags
 *
 * @since 1.0.0
 *
 * @param array     $tags   Registered tags
 *
 * @return array
 */
function bbforms_woocommerce_get_tags( $tags ) {

    $tags['woocommerce'] = array(
        'label' => __( 'WooCommerce', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'cart',
    );

    $tags['woocommerce']['tags']['woocommerce.is_customer'] = array(
        'label'     => __( 'Logged in user is customer', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows "yes" if logged in user is a customer, "no" if not.', 'bbforms' ),
    );

    $tags['woocommerce']['tags']['woocommerce.is_customer.EMAIL'] = array(
        'label'     => __( 'Email is registered as customer', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows "yes" if email is registered as customer, "no" if not.', 'bbforms' )
        . ' ' . __( 'Replace "EMAIL" by the email address to check.', 'bbforms' )
        // translators: %s tag
            . ' ' . sprintf( __( 'You can use a tag for the email like %s.', 'bbforms' ), '{woocommerce.is_customer.{field.email}}' ),
    );

    $tags['woocommerce']['tags']['woocommerce.is_customer.EMAIL.YES.NO'] = array(
        'label'     => __( 'Custom YES or NO for is customer', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows a custom value if email is registered as customer.', 'bbforms' )
            . ' ' . __( 'Replace "EMAIL" by the email address to check.', 'bbforms' )
            . ' ' . __( 'Replace "YES" and "NO" by the values you want .', 'bbforms' )
            . ' ' . __( 'You can leave "NO" empty to only display a text if is customer like {woocommerce.is_customer.EMAIL.(Customer).}.', 'bbforms' ),
    );

    $tags['woocommerce']['tags']['woocommerce.order_history_table'] = array(
        'label'     => __( 'Logged in user order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows a HTML table with the customer orders history including the order number, payment date and payment status.', 'bbforms' ),
    );

    $tags['woocommerce']['tags']['woocommerce.order_history_table.EMAIL'] = array(
        'label'     => __( 'Email order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows a HTML table with the customer orders history including the order number, payment date and payment status.', 'bbforms' )
            . ' ' . __( 'Replace "EMAIL" by the customer email address to retrieve the information.', 'bbforms' )
            // translators: %s tag
            . ' ' . sprintf( __( 'You can use a tag for the email like %s.', 'bbforms' ), '{woocommerce.order_history_table.{field.email}}' ),
    );

    $tags['woocommerce']['tags']['woocommerce.admin_order_history_table'] = array(
        'label'     => __( 'Admin logged in user order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Same as {woocommerce.order_history_table} but including links to access to the customer or order details in the admin area.', 'bbforms' )
            . ' ' . __( 'Useful when your team receives an email and you want to place direct links to quickly view the customer or order details from the admin area.', 'bbforms' ),
    );

    $tags['woocommerce']['tags']['woocommerce.admin_order_history_table.EMAIL'] = array(
        'label'     => __( 'Admin email order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Same as {woocommerce.order_history_table.EMAIL} but including links to access to the customer or order details in the admin area.', 'bbforms' )
            . ' ' . __( 'Replace "EMAIL" by the customer email address to retrieve the information.', 'bbforms' )
            // translators: %s tag
            . ' ' . sprintf( __( 'You can use a tag for the email like %s.', 'bbforms' ), '{woocommerce.admin_order_history_table.{field.email}}' ),
    );

    return $tags;

}
add_filter( 'bbforms_get_tags', 'bbforms_woocommerce_get_tags' );

/**
 * Filter the tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $form           The form
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 *
 * @return string
 */
function bbforms_woocommerce_do_tag( $replacement, $tag_name, $form, $user_id, $content ) {

    if( bbforms_starts_with( $tag_name, 'woocommerce.' ) ) {

        $user = get_userdata( $user_id );

        // Is Customer
        if( $tag_name === 'woocommerce.is_customer' || bbforms_starts_with( $tag_name, 'woocommerce.is_customer.' ) ) {

            $email = ( $user ? $user->user_email : '' );
            $yes = 'yes';
            $no = 'no';

            // woocommerce.is_customer.EMAIL.YES.NO
            if( bbforms_starts_with( $tag_name, 'woocommerce.is_customer.' ) ) {
                $params = explode('.', $tag_name, 3 )[2];
                $parts = explode('.', $params );

                $dots = substr_count( $params, '.' );

                switch( $dots ) {
                    case 1:
                        // bbforms.com
                        $email = $params;
                        break;
                    case 2:
                        // mail.bbforms.com
                        $email = $params;
                        break;
                    case 3:
                        // bbforms.com.YES.NO
                        $email = $parts[0] . '.' . $parts[1];
                        $yes = $parts[2];
                        $no = $parts[3];
                        break;
                    case 4:
                        // mail.bbforms.com.YES.NO
                        $email = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
                        $yes = $parts[3];
                        $no = $parts[4];
                        break;
                }

                $email = sanitize_email( $email );
                $yes = sanitize_text_field( $yes );
                $no = sanitize_text_field( $no );
            }

            $replacement = $no;

            if ( ! empty( $email ) ) {

                // Get customer by email
                $customer = new WC_Customer( $user->ID );

                if ( $customer !== false ) {
                    // Check if customer has at least 1 order completed to consider it as customer
                    $orders = wc_get_orders( array(
                        'customer' => $email,
                        'status' => 'completed',
                        'limit'  => -1,
                    ) );

                    if( count( $orders ) ) {
                        $replacement = $yes;
                    }
                }

            }

            return $replacement;

        }

        // Order History
        if( $tag_name === 'woocommerce.order_history_table' || bbforms_starts_with( $tag_name, 'woocommerce.order_history_table.' ) ) {

            $email = ( $user ? $user->user_email : '' );

            // woocommerce.order_history_table.EMAIL
            if( bbforms_starts_with( $tag_name, 'woocommerce.order_history_table.' ) ) {
                $email_tag = str_replace( 'woocommerce.order_history_table.', '', $tag_name );
                $email_tag = sanitize_email( $email_tag );
                $email = ( ! empty( $email_tag ) ? sanitize_email( $email_tag ) : $email );
            }

            $replacement = '';

            if ( ! empty( $email ) ) {

                $user = get_user_by( 'email', $email );
                // Get customer by email
                $customer = new WC_Customer( $user->ID );

                if ( $customer !== false ) {
                    // Get customer order history
                    $orders = $orders = wc_get_orders( array(
                        'customer' => $email,
                        'limit'  => -1,
                    ) );

                    if( count( $orders ) ) {

                        $replacement = '<table>';

                        // Orders headers
                        $replacement .= '<tr>'
                                . '<td><strong>' . esc_html__( 'Order', 'bbforms' ) . '</strong></td>'
                                . '<td><strong>' . esc_html__( 'Payment Status', 'bbforms' ) . '</strong></td>'
                                . '<td><strong>' . esc_html__( 'Date', 'bbforms' ) . '</strong></td>'
                            . '</tr>';

                        foreach ( $orders as $order ) {

                            $info = bbforms_woocommerce_get_order_info( $order );

                            $replacement .= '<tr>'
                                . '<td>' . $info['number'] . '</td>'
                                . '<td>' . $info['status'] . '</td>'
                                . '<td>' . $info['date'] . '</td>'
                             . '</tr>';

                            foreach( $info['items'] as $item ) {
                                $replacement .= '<tr>'
                                    . '<td colspan=\'3\'>' . $item . '</td>'
                                    . '</tr>';
                            }

                        }

                        $replacement .= '</table>';

                    }
                }

            }

            return $replacement;

        }

        // Admin Order History
        if( $tag_name === 'woocommerce.admin_order_history_table' || bbforms_starts_with( $tag_name, 'woocommerce.admin_order_history_table.' ) ) {

            // Get customer by email
            $email = ( $user ? $user->user_email : '' );

            // woocommerce.admin_order_history_table.EMAIL
            if( bbforms_starts_with( $tag_name, 'woocommerce.admin_order_history_table.' ) ) {
                $email_tag = str_replace( 'woocommerce.admin_order_history_table.', '', $tag_name );
                $email_tag = sanitize_email( $email_tag );
                $email = ( ! empty( $email_tag ) ? sanitize_email( $email_tag ) : $email );
            }

            $replacement = '';

            if ( ! empty( $email ) ) {

                $user = get_user_by( 'email', $email );
                // Get customer by email
                $customer = new WC_Customer( $user->ID );

                if ( $customer !== false ) {
                    // Get customer order history
                    $orders = $orders = wc_get_orders( array(
                        'customer' => $email,
                        'limit'  => -1,
                    ) );

                    if( count( $orders ) ) {

                        $replacement = '<table>';

                        $customer_url = admin_url( 'admin.php?page=wc-admin&path=/customers&filter=single_customer&customers=' . $customer->get_id() );

                        // Customer
                        $replacement .= '<tr>'
                            . '<td colspan=\'3\'><strong>' . esc_html__( 'Customer', 'bbforms' ) . '</strong></td>'
                            . '</tr>';

                        $replacement .= '<tr>'
                            . '<td colspan=\'3\'><a href=\'' . esc_attr( $customer_url ) . '\' target=\'_blank\'>' . $customer->get_first_name() . ' ' . $customer->get_last_name() . '</a></td>'
                            . '</tr>';

                        // Orders headers
                        $replacement .= '<tr>'
                            . '<td><strong>' . esc_html__( 'Order', 'bbforms' ) . '</strong></td>'
                            . '<td><strong>' . esc_html__( 'Payment Status', 'bbforms' ) . '</strong></td>'
                            . '<td><strong>' . esc_html__( 'Date', 'bbforms' ) . '</strong></td>'
                            . '</tr>';

                        foreach ( $orders as $order ) {

                            $info = bbforms_woocommerce_get_order_info( $order );

                            $replacement .= '<tr>'
                                . '<td><a href=\'' . esc_attr( $info['url'] ) . '\' target=\'_blank\'>' . $info['number'] . '</a></td>'
                                . '<td>' . $info['status'] . '</td>'
                                . '<td>' . $info['date'] . '</td>'
                                . '</tr>';

                            foreach( $info['items'] as $item ) {
                                $replacement .= '<tr>'
                                    . '<td colspan=\'3\'>' . $item . '</td>'
                                    . '</tr>';
                            }

                        }

                        $replacement .= '</table>';

                    }
                }

            }

            return $replacement;

        }

    }

    return $replacement;

}
add_filter( 'bbforms_do_tag', 'bbforms_woocommerce_do_tag', 10, 5 );

/**
 * Helper function to get the order information
 *
 * @since 1.0.0
 *
 * @param EDD\Orders\Order $order
 *
 * @return array
 */
function bbforms_woocommerce_get_order_info( $order ) {

    // Admin URL
    $url = $order->get_edit_order_url();

    // Order number
    $number = '#' . $order->get_order_number();

    // Order Status
    $status = $order->get_status();

    // Order Date
    $date = ( $order->get_date_completed() !== null ? $order->get_date_completed()->date('Y-m-d') : $order->get_date_created()->date('Y-m-d') );

    $date_parts = explode( " ", $date );
    $date = ( isset( $date_parts[0] ) ? $date_parts[0] : $date );

    // Order Items
    $items = array();

    foreach( $order->get_items() as $item ) {
        $items[] = $item->get_name();
    }

    return array(
      'url'     =>  $url,
      'number'  =>  $number,
      'status'  =>  $status,
      'date'    =>  $date,
      'items'   =>  $items,
    );

}

/**
 * Adds new tags in the help section
 *
 * @since 1.0.0
 *
 * @param string $output
 *
 * @return string
 */
function bbforms_woocommerce_tags_help_tags_list_content( $output ) {

    $tags = bbforms_get_tags();

    $output .= '<h3>' . esc_html__( 'WooCommerce tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tags to display customer information about logged in user or entered email.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['woocommerce']['tags'] );

    return $output;

}
add_filter( 'bbforms_tags_help_tags_list_content', 'bbforms_woocommerce_tags_help_tags_list_content' );