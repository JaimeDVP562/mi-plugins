<?php
/**
 * Plugin Name:     GamiPress - LifterLMS Partial Payments
 * Plugin URI:      https://gamipress.com/
 * Description:     Permite usar puntos de GamiPress como descuento parcial en LifterLMS.
 * Version:         1.0.0
 * Author:          Tu nombre
 * Text Domain:     gamipress-llms-partial-payments
 * License:         GNU AGPL v3.0
 */

// SEGURIDAD: si alguien intenta abrir este archivo directamente en el navegador, lo bloqueamos
if ( ! defined( 'ABSPATH' ) ) exit;

// Patrón Singleton: garantiza que solo existe UNA instancia del plugin
final class GamiPress_LifterLMS_Partial_Payments {

    private static $instance;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new GamiPress_LifterLMS_Partial_Payments();
            self::$instance->constants();   // Paso 1: definir constantes
            self::$instance->libraries();   // Paso 2: cargar librerías
            self::$instance->includes();    // Paso 3: cargar archivos
            self::$instance->hooks();       // Paso 4: registrar hooks
        }
        return self::$instance;
    }

    // Define valores fijos que usaremos en todo el plugin
    private function constants() {
        define( 'GAMIPRESS_LLMS_PP_VER', '1.0.0' );
        define( 'GAMIPRESS_LLMS_PP_GAMIPRESS_MIN_VER', '3.0.0' );
        define( 'GAMIPRESS_LLMS_PP_FILE', __FILE__ );
        define( 'GAMIPRESS_LLMS_PP_DIR', plugin_dir_path( __FILE__ ) );
        define( 'GAMIPRESS_LLMS_PP_URL', plugin_dir_url( __FILE__ ) );
    }

    // Carga librerías adicionales (solo si se cumplen requisitos)
    private function libraries() {
        if ( $this->meets_requirements() ) {
            require_once GAMIPRESS_LLMS_PP_DIR . 'libraries/points-rate-field-type.php';
        }
    }

    // Carga todos los archivos de la carpeta includes/
    private function includes() {
        if ( $this->meets_requirements() ) {
            require_once GAMIPRESS_LLMS_PP_DIR . 'includes/admin.php';
            require_once GAMIPRESS_LLMS_PP_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_LLMS_PP_DIR . 'includes/filters.php';
            require_once GAMIPRESS_LLMS_PP_DIR . 'includes/functions.php';
            require_once GAMIPRESS_LLMS_PP_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_LLMS_PP_DIR . 'includes/template-functions.php';
        }
    }

    // Registra acciones de activación/desactivación
    private function hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    function activate() {}
    function deactivate() {}

    // Muestra aviso en el panel si faltan plugins requeridos
    public function admin_notices() {
        if ( ! $this->meets_requirements() ) {
            echo '<div class="notice notice-error"><p>';
            echo 'GamiPress - LifterLMS Partial Payments requiere GamiPress y LifterLMS.';
            echo '</p></div>';
        }
    }

    // LA COMPROBACIÓN CLAVE: ¿están GamiPress y LifterLMS instalados y activos?
    private function meets_requirements() {
        return ( class_exists( 'GamiPress' ) && class_exists( 'LifterLMS' ) );
    }
}

// Esta función global activa el plugin cuando WordPress termina de cargar
function GamiPress_LLMS_Partial_Payments() {
    return GamiPress_LifterLMS_Partial_Payments::instance();
}
add_action( 'plugins_loaded', 'GamiPress_LLMS_Partial_Payments' );
