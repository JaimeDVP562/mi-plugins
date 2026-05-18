<?php
/**
 * GamiPress Referrals List Shortcode
 *
 * @package     GamiPress\Referrals\Shortcodes\Shortcode\GamiPress_Referrals_List
 * @since       1.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_referrals_list] shortcode.
 *
 * @since 1.2.0
 */
function gamipress_register_referrals_list_shortcode() {

    gamipress_register_shortcode( 'gamipress_referrals_list', array(
        'name'              => __( 'Referrals list', 'gamipress-referrals' ),
        'description'       => __( 'Display a list of referrals for the current user.', 'gamipress-referrals' ),
        'output_callback'   => 'gamipress_referrals_list_shortcode',
        'icon'              => 'groups',
        'fields'            => array(

            'type' => array(
                'name'        => __( 'Type', 'gamipress-referrals' ),
                'description' => __( 'Filter referrals by type. Leave empty to show all.', 'gamipress-referrals' ),
                'type'        => 'select',
                'options'     => array(
                    ''                      => __( 'All', 'gamipress-referrals' ),
                    'referral_visit'        => __( 'Visits', 'gamipress-referrals' ),
                    'referral_signup'       => __( 'Sign Ups', 'gamipress-referrals' ),
                    'referral_sale'         => __( 'Sales', 'gamipress-referrals' ),
                    'referral_sale_refund'  => __( 'Sales Refunded', 'gamipress-referrals' ),
                ),
                'default'     => '',
            ),
            'limit' => array(
                'name'        => __( 'Limit', 'gamipress-referrals' ),
                'description' => __( 'Number of referrals to display. Set to 0 to show all.', 'gamipress-referrals' ),
                'type'        => 'text',
                'default'     => '20',
            ),
            'current_user' => array(
                'name'        => __( 'Current User', 'gamipress-referrals' ),
                'description' => __( 'Show referrals of the current logged in user.', 'gamipress-referrals' ),
                'type'        => 'checkbox',
                'classes'     => 'gamipress-switch',
                'default'     => 'yes',
            ),
            'user_id' => array(
                'name'        => __( 'User', 'gamipress-referrals' ),
                'description' => __( 'Show referrals of a specific user.', 'gamipress-referrals' ),
                'type'        => 'select',
                'classes'     => 'gamipress-user-selector',
                'default'     => '',
                'options_cb'  => 'gamipress_options_cb_users'
            ),

        ),
    ) );

}
add_action( 'init', 'gamipress_register_referrals_list_shortcode' );

/**
 * Referrals List Shortcode.
 *
 * @since  1.2.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string      HTML markup.
 */
function gamipress_referrals_list_shortcode( $atts = array() ) {

    $atts = shortcode_atts( array(

        'type'              => '',
        'limit'             => '20',
        'current_user'      => 'yes',
        'user_id'           => '0',

    ), $atts, 'gamipress_referrals_list' );

    // Force to set current user as user ID
    if( $atts['current_user'] === 'yes' ) {
        $atts['user_id'] = get_current_user_id();
    }

    // ---------------------------
    // Shortcode Errors
    // ---------------------------

    // Return if not logged in and current user
    if( $atts['current_user'] === 'yes' && ! is_user_logged_in() ) {
        return '';
    }

    // Return if user id not specified
    if( $atts['current_user'] === 'no' && absint( $atts['user_id'] ) === 0 ) {
        return gamipress_shortcode_error( __( 'Please, provide the user id.', 'gamipress-referrals' ), 'gamipress_referrals_list' );
    }

    // ---------------------------
    // Shortcode Processing
    // ---------------------------

    $user_id = absint( $atts['user_id'] );
    $limit = absint( $atts['limit'] );

    $type_labels = array(
        'referral_visit'        => __( 'Visit', 'gamipress-referrals' ),
        'referral_signup'       => __( 'Sign Up', 'gamipress-referrals' ),
        'referral_sale'         => __( 'Sale', 'gamipress-referrals' ),
        'referral_sale_refund'  => __( 'Sale Refund', 'gamipress-referrals' ),
    );

    // ARREGLO SENIOR: Bypasseamos llamadas a funciones de upgrades.php que darían Error 500 en el Frontend.
    $is_upgraded = version_compare( get_option( 'gamipress_referrals_version', '1.0.0' ), '1.2.0', '>=' );

    // Check if migrated to use new table, otherwise fall back to logs
    if( $is_upgraded ) {
        // Query from custom table
        $query_args = array(
            'user_id'   => $user_id,
            'orderby'   => 'date',
            'order'     => 'DESC',
            'limit'     => $limit > 0 ? $limit : -1,
        );

        if( ! empty( $atts['type'] ) ) {
            $query_args['type'] = $atts['type'];
        }

        $referrals = gamipress_referrals_query_referrals( $query_args );
    } else {
        // Query from logs (fallback)
        $referrals = gamipress_referrals_list_query_from_logs( $user_id, $atts['type'], $limit );
    }

    // Start output
    ob_start();

    if( empty( $referrals ) ) {
        echo '<p class="gamipress-referrals-no-results">' . __( 'No referrals found.', 'gamipress-referrals' ) . '</p>';
    } else {
        ?>
        <div class="gamipress-referrals-list">
            <table class="gamipress-referrals-list-table">
                <thead>
                    <tr>
                        <th><?php _e( 'User', 'gamipress-referrals' ); ?></th>
                        <th><?php _e( 'Type', 'gamipress-referrals' ); ?></th>
                        <th><?php _e( 'Date', 'gamipress-referrals' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach( $referrals as $referral ) :

                        // Handle different data structures (CT object vs logs)
                        if( $is_upgraded ) {
                            $ref_user_id = absint( $referral->referral_user_id );
                            $ref_type = $referral->type;
                            $ref_date = $referral->date;
                        } else {
                            $ref_user_id = absint( isset( $referral->referral_user_id ) ? $referral->referral_user_id : 0 );
                            $ref_type = $referral->type;
                            $ref_date = $referral->date;
                        }

                        $ref_user = ( $ref_user_id > 0 ) ? get_userdata( $ref_user_id ) : false;
                        $ref_type_label = isset( $type_labels[ $ref_type ] ) ? $type_labels[ $ref_type ] : $ref_type;
                    ?>
                        <tr>
                            <td>
                                <?php if( $ref_user ) : ?>
                                    <?php echo esc_html( $ref_user->display_name ); ?>
                                <?php else : ?>
                                    <em><?php _e( 'Guest', 'gamipress-referrals' ); ?></em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $ref_type_label ); ?></td>
                            <td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $ref_date ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <style>
            .gamipress-referrals-list-table {
                width: 100%;
                border-collapse: collapse;
            }
            .gamipress-referrals-list-table th,
            .gamipress-referrals-list-table td {
                padding: 8px 12px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            .gamipress-referrals-list-table th {
                background-color: #f5f5f5;
                font-weight: 600;
            }
            .gamipress-referrals-list-table tbody tr:hover {
                background-color: #f9f9f9;
            }
        </style>
        <?php
    }

    $output = ob_get_clean();

    /**
     * Filter to return a custom output
     *
     * @since 1.2.0
     *
     * @param string    $output     Shortcode output
     * @param int       $user_id    User ID
     * @param array     $atts       Shortcode attributes
     *
     * @return string
     */
    $output = apply_filters( 'gamipress_referrals_list_shortcode_output', $output, $user_id, $atts );

    return $output;

}

/**
 * Query referrals from GamiPress logs (fallback for non-migrated sites)
 *
 * @since 1.2.0
 *
 * @param int       $user_id    User ID
 * @param string    $type       Type filter
 * @param int       $limit      Limit
 *
 * @return array
 */
function gamipress_referrals_list_query_from_logs( $user_id, $type = '', $limit = 20 ) {

    global $wpdb;

    // ARREGLO SENIOR: Usamos $wpdb->prefix en lugar de funciones que pueden no estar cargadas en Frontend.
    $logs_table = $wpdb->prefix . 'gamipress_logs';
    $logs_meta_table = $wpdb->prefix . 'gamipress_logs_meta';

    $referral_types = array( 'referral_visit', 'referral_signup', 'referral_sale', 'referral_sale_refund' );

    $where = $wpdb->prepare( "l.user_id = %d", $user_id );

    if( ! empty( $type ) && in_array( $type, $referral_types ) ) {
        $where .= $wpdb->prepare( " AND l.type = %s", $type );
    } else {
        $types_in = "'" . implode( "','", $referral_types ) . "'";
        $where .= " AND l.type IN ({$types_in})";
    }

    $limit_clause = '';
    if( $limit > 0 ) {
        $limit_clause = $wpdb->prepare( "LIMIT %d", $limit );
    }

    $results = $wpdb->get_results(
        "SELECT l.*, lm.meta_value as referral_user_id
        FROM {$logs_table} l
        LEFT JOIN {$logs_meta_table} lm ON l.log_id = lm.log_id AND lm.meta_key = '_gamipress_referral_id'
        WHERE {$where}
        ORDER BY l.date DESC
        {$limit_clause}"
    );

    return $results ? $results : array();

}