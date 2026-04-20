<?php
// File: includes/class-googlegroups-client-factory.php
class Google_Groups_Client_Factory {

    public static function get_client() {
        // Si la opción de test está activada, devuelve el mock
        $test_mode = get_option( 'automatorwp_googlegroups_test_mode', 0 );
        if ( $test_mode ) {
            if ( ! class_exists( 'Google_Groups_Client_Mock' ) ) {
                include_once plugin_dir_path( __FILE__ ) . 'class-googlegroups-client-mock.php';
            }
            return new Google_Groups_Client_Mock();
        }

        // TODO: devolver la implementación real del cliente que usa Google API.
        // Por ahora devuelve null para evitar llamadas reales si no está implementado.
        return null;
    }
}
