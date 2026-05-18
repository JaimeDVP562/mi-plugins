<?php
/**
 * Admin
 *
 * @package GamiPress\LearnDash\Points_Gateway\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'gamipress_learndash_add_setting_page');

function gamipress_learndash_add_setting_page(){
    add_submenu_page( 'gamipress', 'Ajustes LearnDash Gateway', 'Ajustes LearnDash', 'manage_options', 'gamipress-learndash-settings', 'gamipress_learndash_render_settings_page');
}

function gamipress_learndash_render_settings_page(){
    if (isset($_POST['gamipress_ld_save_settings'])) {
        $new_point_type = sanitize_text_field( $_POST['point_type']);
        update_option('gamipress_ld_point_type', $new_point_type);
        $new_exchange_rate = intval ($_POST['exchange_rate']);
        update_option( 'gamipress_ld_exchange_rate', $new_exchange_rate );
        echo '<div class="notice notice-success is-dismissible"> <p>Ajustes de LearnDash guardados correctamente.</p> </div>';
    }

    $current_point_type = get_option('gamipress_ld_point_type', 'credits');
    $current_exchange_rate = get_option('gamipress_ld_exchange_rate', 100);

    ?>
    
    <div class="wrap">
        <h1>Ajustes GamiPress y LearnDash</h1>
        <p>Configuración de cobros del curso</p>
        <form method="POST" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"> <label for="point_type">Tipo de Moneda(ej. créditos, gemas)</label> </th>
                    <td>
                        <input name="point_type" type="text" id="point_type" value="<?php echo esc_attr( $current_point_type ); ?>" class="regular-text">
                        <p class="description"> Poner el nombre exacto que se configure en los tipos de punto de Gamipress.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"> <label for="exchange_rate">Multiplicador de precio</label></th>
                    <td>
                        1 Euro = <input name="exchange_rate" type="number" id="exchange_rate" value="<?php echo esc_attr( $current_exchange_rate ); ?>" class="small-text"> Puntos
                        <p class="description">Si el curso vale 50€ y el multiplicador es 100, costará 5000 puntos.</p>
                    </td>
                </tr>
            </table>

            <input type="hidden" name="gamipress_ld_save_settings" value="1">
            <p class="submit">
                <button type="submit" class="button button-primary">Guardar Ajustes</button>
            </p>
        </form>

    </div>

<?php
}