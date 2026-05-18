<?php
/**
 * Custom Tables Views
 *
 * @package GamiPress\Referrals\Custom_Tables_Views
 * @since 1.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register custom tables views meta boxes
 *
 * @since 1.2.0
 */
function gamipress_referrals_add_meta_boxes() {

    // Only add meta boxes on the referrals edit view
    if( ! ( function_exists( 'ct_get_table_object' ) ) ) return;

    $ct_table = ct_get_table_object( 'gamipress_referrals_referrals' );

    if( ! $ct_table ) return;

    if( $ct_table->name !== 'gamipress_referrals_referrals' ) return;

    add_meta_box(
        'gamipress-referrals-details',
        __( 'Referral Details', 'gamipress-referrals' ),
        'gamipress_referrals_details_meta_box',
        'gamipress_referrals_referrals',
        'normal',
        'high'
    );

    add_meta_box(
        'gamipress-referrals-actions',
        __( 'Actions', 'gamipress-referrals' ),
        'gamipress_referrals_actions_meta_box',
        'gamipress_referrals_referrals',
        'side',
        'high'
    );

}
add_action( 'add_meta_boxes', 'gamipress_referrals_add_meta_boxes' );

/**
 * Referral details meta box
 *
 * @since 1.2.0
 *
 * @param stdClass $object
 */
function gamipress_referrals_details_meta_box( $object ) {

    $affiliate = get_userdata( $object->user_id );
    $referral_user = ( absint( $object->referral_user_id ) > 0 ) ? get_userdata( $object->referral_user_id ) : false;

    $type_labels = array(
        'referral_visit'        => __( 'Referral Visit', 'gamipress_referrals_referrals' ),
        'referral_signup'       => __( 'Referral Sign Up', 'gamipress_referrals_referrals' ),
        'referral_sale'         => __( 'Referral Sale', 'gamipress_referrals_referrals' ),
        'referral_sale_refund'  => __( 'Referral Sale Refund', 'gamipress_referrals_referrals' ),
    );

    $type_label = isset( $type_labels[ $object->type ] ) ? $type_labels[ $object->type ] : $object->type;
    ?>

    <table class="form-table">
        <tr>
            <th><label><?php _e( 'Affiliate', 'gamipress_referrals_referrals' ); ?>:</label></th>
            <td>
                <?php if( $affiliate ) : ?>
                    <?php if( current_user_can( 'edit_users' ) ) : ?>
                        <a href="<?php echo get_edit_user_link( $affiliate->ID ); ?>"><?php echo $affiliate->display_name; ?></a>
                        <br><small><?php echo $affiliate->user_email; ?></small>
                    <?php else : ?>
                        <?php echo $affiliate->display_name; ?>
                    <?php endif; ?>
                <?php else : ?>
                    <em><?php _e( 'User not found', 'gamipress_referrals_referrals' ); ?></em>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label><?php _e( 'Referred User', 'gamipress_referrals_referrals' ); ?>:</label></th>
            <td>
                <?php if( $referral_user ) : ?>
                    <?php if( current_user_can( 'edit_users' ) ) : ?>
                        <a href="<?php echo get_edit_user_link( $referral_user->ID ); ?>"><?php echo $referral_user->display_name; ?></a>
                        <br><small><?php echo $referral_user->user_email; ?></small>
                    <?php else : ?>
                        <?php echo $referral_user->display_name; ?>
                    <?php endif; ?>
                <?php else : ?>
                    <em><?php _e( 'Guest visitor', 'gamipress_referrals_referrals' ); ?></em>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label><?php _e( 'IP Address', 'gamipress_referrals_referrals' ); ?>:</label></th>
            <td><?php echo esc_html( $object->referral_ip ); ?></td>
        </tr>
        <tr>
            <th><label><?php _e( 'Type', 'gamipress_referrals_referrals' ); ?>:</label></th>
            <td><?php echo esc_html( $type_label ); ?></td>
        </tr>
        <?php if( ! empty( $object->post_url ) ) : ?>
            <tr>
                <th><label><?php _e( 'URL', 'gamipress_referrals_referrals' ); ?>:</label></th>
                <td><a href="<?php echo esc_url( $object->post_url ); ?>" target="_blank"><?php echo esc_html( $object->post_url ); ?></a></td>
            </tr>
        <?php endif; ?>
        <?php if( ! empty( $object->referrer ) ) : ?>
            <tr>
                <th><label><?php _e( 'Referrer URL', 'gamipress_referrals_referrals' ); ?>:</label></th>
                <td><a href="<?php echo esc_url( $object->referrer ); ?>" target="_blank"><?php echo esc_html( $object->referrer ); ?></a></td>
            </tr>
        <?php endif; ?>
        <?php if( ! empty( $object->integration ) ) : ?>
            <tr>
                <th><label><?php _e( 'Integration', 'gamipress_referrals_referrals' ); ?>:</label></th>
                <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $object->integration ) ) ); ?></td>
            </tr>
        <?php endif; ?>
        <?php if( absint( $object->post_id ) > 0 && in_array( $object->type, array( 'referral_sale', 'referral_sale_refund' ) ) ) : ?>
            <tr>
                <th><label><?php _e( 'Order/Sale ID', 'gamipress_referrals_referrals' ); ?>:</label></th>
                <td>#<?php echo absint( $object->post_id ); ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th><label><?php _e( 'Date', 'gamipress_referrals_referrals' ); ?>:</label></th>
            <td><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $object->date ) ); ?></td>
        </tr>
    </table>

    <?php
}

/**
 * Referral actions meta box (with delete button)
 *
 * @since 1.2.0
 *
 * @param stdClass $object
 */
function gamipress_referrals_actions_meta_box( $object ) {
    ?>
    <div class="submitbox">
        <div id="major-publishing-actions">
            <div id="delete-action">
                <?php
                $delete_url = ct_get_delete_link( 'gamipress_referrals_referrals', $object->referral_id );
                ?>
                <a class="submitdelete deletion" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this referral?', 'gamipress-referrals' ) ); ?>');">
                    <?php _e( 'Delete Referral', 'gamipress_referrals_referrals' ); ?>
                </a>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <?php
}

/**
 * Manage referrals list table columns
 *
 * @since 1.2.0
 *
 * @param array $columns
 *
 * @return array
 */
function gamipress_referrals_manage_columns( $columns = array() ) {

    $columns['referral_id']      = __( 'ID', 'gamipress_referrals_referrals' );
    $columns['affiliate']        = __( 'Affiliate', 'gamipress_referrals_referrals' );
    $columns['referral_user']    = __( 'Referred User', 'gamipress_referrals_referrals' );
    $columns['type']             = __( 'Type', 'gamipress_referrals_referrals' );
    $columns['referral_ip']      = __( 'IP', 'gamipress_referrals_referrals' );
    $columns['date']             = __( 'Date', 'gamipress_referrals_referrals' );

    return $columns;
}
add_filter( 'manage_gamipress_referrals_referrals_columns', 'gamipress_referrals_manage_columns' );

/**
 * Manage referrals list table sortable columns
 *
 * @since 1.2.0
 *
 * @param array $columns
 *
 * @return array
 */
function gamipress_referrals_manage_sortable_columns( $columns = array() ) {

    $columns['referral_id']  = array( 'referral_id', false );
    $columns['type']         = array( 'type', false );
    $columns['date']         = array( 'date', true );

    return $columns;
}
add_filter( 'manage_gamipress_referrals_referrals_sortable_columns', 'gamipress_referrals_manage_sortable_columns' );

/**
 * Render referrals list table columns
 *
 * @since 1.2.0
 *
 * @param string    $column_output
 * @param string    $column_name
 * @param stdClass  $object
 *
 * @return string
 */
function gamipress_referrals_columns_output( $column_output, $column_name, $object ) {

    $type_labels = array(
        'referral_visit'        => __( 'Visit', 'gamipress_referrals_referrals' ),
        'referral_signup'       => __( 'Sign Up', 'gamipress_referrals_referrals' ),
        'referral_sale'         => __( 'Sale', 'gamipress_referrals_referrals' ),
        'referral_sale_refund'  => __( 'Sale Refund', 'gamipress_referrals_referrals' ),
    );

    switch( $column_name ) {
        case 'referral_id':
            $column_output = $object->referral_id;
            break;
        case 'affiliate':
            $user = get_userdata( $object->user_id );
            if( $user ) {
                if( current_user_can( 'edit_users' ) ) {
                    $column_output = '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->display_name . '</a>';
                } else {
                    $column_output = $user->display_name;
                }
            } else {
                $column_output = '<em>' . __( 'User not found', 'gamipress_referrals_referrals' ) . '</em>';
            }
            break;
        case 'referral_user':
            if( absint( $object->referral_user_id ) > 0 ) {
                $user = get_userdata( $object->referral_user_id );
                if( $user ) {
                    if( current_user_can( 'edit_users' ) ) {
                        $column_output = '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->display_name . '</a>';
                    } else {
                        $column_output = $user->display_name;
                    }
                } else {
                    $column_output = '<em>' . __( 'User not found', 'gamipress_referrals_referrals' ) . '</em>';
                }
            } else {
                $column_output = '<em>' . __( 'Guest', 'gamipress_referrals_referrals' ) . '</em>';
            }
            break;
        case 'type':
            $column_output = isset( $type_labels[ $object->type ] ) ? $type_labels[ $object->type ] : $object->type;
            break;
        case 'referral_ip':
            $column_output = esc_html( $object->referral_ip );
            break;
        case 'date':
            $column_output = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $object->date ) );
            break;
    }

    return $column_output;
}
add_filter( 'manage_gamipress_referrals_referrals_custom_column', 'gamipress_referrals_columns_output', 10, 3 );
