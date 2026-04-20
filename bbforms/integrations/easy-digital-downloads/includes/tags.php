<?php
/**
 * Tags
 *
 * @package     BBForms\Easy_Digital_Downloads\Tags
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
function bbforms_easy_digital_downloads_get_tags( $tags ) {

    $tags['easy_digital_downloads'] = array(
        'label' => __( 'EDD', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'download',
    );

    $tags['easy_digital_downloads']['tags']['edd.is_customer'] = array(
        'label'     => __( 'Logged in user is customer', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows "yes" if logged in user is a customer, "no" if not.', 'bbforms' ),
    );

    $tags['easy_digital_downloads']['tags']['edd.is_customer.EMAIL'] = array(
        'label'     => __( 'Email is registered as customer', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows "yes" if email is registered as customer, "no" if not.', 'bbforms' )
        . ' ' . __( 'Replace "EMAIL" by the email address to check.', 'bbforms' )
        // translators: %s tag
            . ' ' . sprintf( __( 'You can use a tag for the email like %s.', 'bbforms' ), '{edd.is_customer.{field.email}}' ),
    );

    $tags['easy_digital_downloads']['tags']['edd.is_customer.EMAIL.YES.NO'] = array(
        'label'     => __( 'Custom YES or NO for is customer', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows a custom value if email is registered as customer.', 'bbforms' )
            . ' ' . __( 'Replace "EMAIL" by the email address to check.', 'bbforms' )
            . ' ' . __( 'Replace "YES" and "NO" by the values you want .', 'bbforms' )
            . ' ' . __( 'You can leave "NO" empty to only display a text if is customer like {edd.is_customer.EMAIL.(Customer).}.', 'bbforms' ),
    );

    $tags['easy_digital_downloads']['tags']['edd.order_history_table'] = array(
        'label'     => __( 'Logged in user order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows a HTML table with the customer orders history including the order number, payment date and payment status.', 'bbforms' ),
    );

    $tags['easy_digital_downloads']['tags']['edd.order_history_table.EMAIL'] = array(
        'label'     => __( 'Email order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Shows a HTML table with the customer orders history including the order number, payment date and payment status.', 'bbforms' )
            . ' ' . __( 'Replace "EMAIL" by the customer email address to retrieve the information.', 'bbforms' )
            // translators: %s tag
            . ' ' . sprintf( __( 'You can use a tag for the email like %s.', 'bbforms' ), '{edd.order_history_table.{field.email}}' ),
    );

    $tags['easy_digital_downloads']['tags']['edd.admin_order_history_table'] = array(
        'label'     => __( 'Admin logged in user order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Same as {edd.order_history_table} but including links to access to the customer or order details in the admin area.', 'bbforms' )
            . ' ' . __( 'Useful when your team receives an email and you want to place direct links to quickly view the customer or order details from the admin area.', 'bbforms' ),
    );

    $tags['easy_digital_downloads']['tags']['edd.admin_order_history_table.EMAIL'] = array(
        'label'     => __( 'Admin email order history', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Same as {edd.order_history_table.EMAIL} but including links to access to the customer or order details in the admin area.', 'bbforms' )
            . ' ' . __( 'Replace "EMAIL" by the customer email address to retrieve the information.', 'bbforms' )
            // translators: %s tag
            . ' ' . sprintf( __( 'You can use a tag for the email like %s.', 'bbforms' ), '{edd.admin_order_history_table.{field.email}}' ),
    );

    return $tags;

}
add_filter( 'bbforms_get_tags', 'bbforms_easy_digital_downloads_get_tags' );

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
function bbforms_easy_digital_downloads_do_tag( $replacement, $tag_name, $form, $user_id, $content ) {

    if( bbforms_starts_with( $tag_name, 'edd.' ) ) {

        $user = get_userdata( $user_id );

        // Is Customer
        if( $tag_name === 'edd.is_customer' || bbforms_starts_with( $tag_name, 'edd.is_customer.' ) ) {

            $email = ( $user ? $user->user_email : '' );
            $yes = 'yes';
            $no = 'no';

            // edd.is_customer.EMAIL.YES.NO
            if( bbforms_starts_with( $tag_name, 'edd.is_customer.' ) ) {
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
                $customer = edd_get_customer_by( 'email', $email );

                if ( $customer !== false ) {
                    // Check if customer has at least 1 order completed to consider it as customer
                    $orders = $customer->get_orders( array( 'complete' ) );

                    if( count( $orders ) ) {
                        $replacement = $yes;
                    }
                }

            }

            return $replacement;

        }

        // Order History
        if( $tag_name === 'edd.order_history_table' || bbforms_starts_with( $tag_name, 'edd.order_history_table.' ) ) {

            $email = ( $user ? $user->user_email : '' );

            // edd.order_history_table.EMAIL
            if( bbforms_starts_with( $tag_name, 'edd.order_history_table.' ) ) {
                $email_tag = str_replace( 'edd.order_history_table.', '', $tag_name );
                $email_tag = sanitize_email( $email_tag );
                $email = ( ! empty( $email_tag ) ? sanitize_email( $email_tag ) : $email );
            }

            $replacement = '';

            if ( ! empty( $email ) ) {

                // Get customer by email
                $customer = edd_get_customer_by( 'email', $email );

                if ( $customer !== false ) {
                    // Get customer order history
                    $orders = $customer->get_orders();

                    if( count( $orders ) ) {

                        $replacement = '<table>';

                        // Orders headers
                        $replacement .= '<tr>'
                                . '<td><strong>' . esc_html__( 'Order', 'bbforms' ) . '</strong></td>'
                                . '<td><strong>' . esc_html__( 'Payment Status', 'bbforms' ) . '</strong></td>'
                                . '<td><strong>' . esc_html__( 'Date', 'bbforms' ) . '</strong></td>'
                            . '</tr>';

                        foreach ( $orders as $order ) {

                            $info = bbforms_easy_digital_downloads_get_order_info( $order );

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
        if( $tag_name === 'edd.admin_order_history_table' || bbforms_starts_with( $tag_name, 'edd.admin_order_history_table.' ) ) {

            // Get customer by email
            $email = ( $user ? $user->user_email : '' );

            // edd.admin_order_history_table.EMAIL
            if( bbforms_starts_with( $tag_name, 'edd.admin_order_history_table.' ) ) {
                $email_tag = str_replace( 'edd.admin_order_history_table.', '', $tag_name );
                $email_tag = sanitize_email( $email_tag );
                $email = ( ! empty( $email_tag ) ? sanitize_email( $email_tag ) : $email );
            }

            $replacement = '';

            if ( ! empty( $email ) ) {

                // Get customer by email
                $customer = edd_get_customer_by( 'email', $email );

                if ( $customer !== false ) {
                    // Get customer order history
                    $orders = $customer->get_orders();

                    if( count( $orders ) ) {

                        $replacement = '<table>';

                        $customer_url = edd_get_admin_url( array(
                            'page' => 'edd-customers',
                            'view' => 'overview',
                            'id' => urlencode( $customer->id ),
                        ) );

                        // Customer
                        $replacement .= '<tr>'
                            . '<td colspan=\'3\'><strong>' . esc_html__( 'Customer', 'bbforms' ) . '</strong></td>'
                            . '</tr>';

                        $replacement .= '<tr>'
                            . '<td colspan=\'3\'><a href=\'' . esc_attr( $customer_url ) . '\' target=\'_blank\'>' . $customer->name . '</a></td>'
                            . '</tr>';

                        // Orders headers
                        $replacement .= '<tr>'
                            . '<td><strong>' . esc_html__( 'Order', 'bbforms' ) . '</strong></td>'
                            . '<td><strong>' . esc_html__( 'Payment Status', 'bbforms' ) . '</strong></td>'
                            . '<td><strong>' . esc_html__( 'Date', 'bbforms' ) . '</strong></td>'
                            . '</tr>';

                        foreach ( $orders as $order ) {

                            $info = bbforms_easy_digital_downloads_get_order_info( $order );

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
add_filter( 'bbforms_do_tag', 'bbforms_easy_digital_downloads_do_tag', 10, 5 );

/**
 * Helper function to get the order information
 *
 * @since 1.0.0
 *
 * @param EDD\Orders\Order $order
 *
 * @return array
 */
function bbforms_easy_digital_downloads_get_order_info( $order ) {

    // Admin URL
    $url = edd_get_admin_url( array(
        'page' => 'edd-payment-history',
        'view' => 'view-order-details',
        'id' => urlencode( $order->ID ),
    ), );

    // Order number
    $number = '#' . $order->get_number();

    // Support for EDD - Sequential Order Numbers
    if( class_exists( 'EDD_Son' ) ) {
        $ason_number = edd_get_order_meta( $order->ID, '_edd_son_payment_number', true );

        if( ! empty( $ason_number ) ) {
            $number = $ason_number;
        }
    }

    // Order Status
    $status = edd_get_payment_status_label( $order->status );

    // Order Date
    $date = ( $order->date_completed !== null ? $order->date_completed : $order->date_created );

    $date_parts = explode( " ", $date );
    $date = ( isset( $date_parts[0] ) ? $date_parts[0] : $date );

    // Order Items
    $items = array();

    foreach( $order->get_items() as $item ) {
        $items[] = $item->product_name;
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
function bbforms_easy_digital_downloads_tags_help_tags_list_content( $output ) {

    $tags = bbforms_get_tags();

    $output .= '<h3>' . esc_html__( 'Easy Digital Downloads tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tags to display customer information about logged in user or entered email.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['easy_digital_downloads']['tags'] );

    return $output;

}
add_filter( 'bbforms_tags_help_tags_list_content', 'bbforms_easy_digital_downloads_tags_help_tags_list_content' );