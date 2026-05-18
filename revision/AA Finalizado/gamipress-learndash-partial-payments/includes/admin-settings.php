<?php
if( !defined( 'ABSPATH' ) ) exit;

function gamipress_ld_register_settings() {
    register_setting( 'gamipress_ld_options_group', 'gamipress_ld_point_type' );
    register_setting( 'gamipress_ld_options_group', 'gamipress_ld_conversion_rate' );
    register_setting( 'gamipress_ld_options_group', 'gamipress_ld_max_discount_pct' );
}
add_action( 'admin_init', 'gamipress_ld_register_settings' );

function gamipress_ld_add_options_page() {
    add_options_page(
        'GamiPress LearnDash Payments', 
        'GamiPress LD Payments', 
        'manage_options', 
        'gamipress-ld-payments', 
        'gamipress_ld_options_page_html'
    );
}
add_action( 'admin_menu', 'gamipress_ld_add_options_page' );

function gamipress_ld_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $point_types         = function_exists( 'gamipress_get_points_types' ) ? gamipress_get_points_types() : array();
    $selected_point_type = get_option( 'gamipress_ld_point_type', 'points' );
    $conversion_rate     = get_option( 'gamipress_ld_conversion_rate', '1' );
    $max_discount_pct    = get_option( 'gamipress_ld_max_discount_pct', '100' );
    
    ?>
    <div class="wrap">
        <h1>GamiPress LearnDash Partial Payments</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'gamipress_ld_options_group' );
            do_settings_sections( 'gamipress_ld_options_group' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Select Point Type for Discounts</th>
                    <td>
                        <select name="gamipress_ld_point_type">
                            <option value="allow_user_choice" <?php selected( $selected_point_type, 'allow_user_choice' ); ?>>-- Allow user to choose at checkout --</option>
                            <?php 
                            if ( ! empty( $point_types ) ) {
                                foreach ( $point_types as $key => $type ) {
                                    $slug  = '';
                                    $title = '';
                                    
                                    if ( is_object( $type ) ) {
                                        $slug  = isset( $type->post_name ) ? $type->post_name : ( isset( $type->name ) ? $type->name : $key );
                                        $title = isset( $type->post_title ) ? $type->post_title : ( isset( $type->label ) ? $type->label : $key );
                                    } elseif ( is_array( $type ) ) {
                                        $slug  = isset( $type['post_name'] ) ? $type['post_name'] : ( isset( $type['slug'] ) ? $type['slug'] : $key );
                                        $title = isset( $type['post_title'] ) ? $type['post_title'] : ( isset( $type['plural_name'] ) ? $type['plural_name'] : ( isset( $type['title'] ) ? $type['title'] : $key ) );
                                    } else {
                                        $slug  = $key;
                                        $title = $type;
                                    }
                                    
                                    if ( empty( $slug ) ) continue;
                                    
                                    echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $selected_point_type, $slug, false ) . '>' . esc_html( $title ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <p class="description">Choose a specific point type, or let the user decide which points to spend.</p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Conversion Rate (€/$)</th>
                    <td>
                        <input type="number" name="gamipress_ld_conversion_rate" value="<?php echo esc_attr( $conversion_rate ); ?>" step="0.01" min="0.01" style="width: 150px;">
                        <p class="description">How much money is 1 point worth? (e.g. 0.10 means 1 point = €0.10 discount).</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Maximum Discount (%)</th>
                    <td>
                        <input type="number" name="gamipress_ld_max_discount_pct" value="<?php echo esc_attr( $max_discount_pct ); ?>" step="1" min="1" max="100" style="width: 150px;">
                        <p class="description">Maximum percentage of the course price that can be paid with points (1-100).</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}