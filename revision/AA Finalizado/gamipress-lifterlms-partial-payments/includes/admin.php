<?php
// Archivo: includes/admin.php
if ( ! defined( 'ABSPATH' ) ) exit;

// Función de acceso rápido a las opciones del plugin
function gamipress_llms_pp_get_option( $option_name, $default = false ) {
    return gamipress_get_option( 'gamipress_llms_pp_' . $option_name, $default );
}

// Añade la sección de configuración en GamiPress → Ajustes
function gamipress_llms_pp_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'gamipress_llms_pp_';

    $meta_boxes['gamipress-llms-pp-settings'] = array(
        'title'  => 'LifterLMS Partial Payments',
        'fields' => array(

            // Tipo de campo: input, slider o fixed
            $prefix . 'amount_type' => array(
                'name'    => 'Tipo de campo de cantidad',
                'desc'    => 'Input: el usuario escribe. Slider: deslizador. Fixed: fijo.',
                'type'    => 'select',
                'options' => array(
                    'input'  => 'Input',
                    'slider' => 'Slider',
                    'fixed'  => 'Fixed',
                ),
                'default' => 'input',
            ),

            // Paso del campo (granularidad)
            $prefix . 'amount_step' => array(
                'name'       => 'Paso del campo',
                'desc'       => 'Paso=1 permite cualquier valor. Paso=10 solo múltiplos de 10.',
                'type'       => 'text',
                'attributes' => array( 'type' => 'number', 'min' => '0', 'step' => '1' ),
                'default'    => '1',
            ),

            // Descuento máximo permitido
            $prefix . 'max_discount' => array(
                'name'       => 'Descuento máximo por compra',
                'desc'       => 'Deja en 0 para no limitar.',
                'type'       => 'text',
                'attributes' => array( 'type' => 'number', 'min' => '0', 'step' => '1' ),
            ),
        )
    );
    return $meta_boxes;
}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_llms_pp_settings_meta_boxes' );


// Añade opciones en la pantalla de edición de cada tipo de puntos
function gamipress_llms_pp_meta_boxes() {
    $prefix = '_gamipress_llms_pp_';

    gamipress_add_meta_box(
        'gamipress-llms-pp',
        'LifterLMS Partial Payments',
        'points-type',  // Aparece en la edición de tipos de puntos
        array(

            // Checkbox para habilitar este tipo de puntos
            $prefix . 'enable' => array(
                'name'    => 'Habilitar Pagos Parciales en LifterLMS',
                'desc'    => 'Marca esta opción para que este tipo de puntos pueda usarse.',
                'type'    => 'checkbox',
                'classes' => 'gamipress-switch',
            ),

            // Campo de tasa de conversión puntos ↔ dinero
            $prefix . 'conversion' => array(
                'name'            => 'Tasa de conversión',
                'desc'            => 'Cuántos puntos equivalen a cuánto dinero.',
                'type'            => 'points_rate',  // Campo especial de la librería
                'currency_symbol' => get_lifterlms_currency_symbol(),
            ),

            // Cantidad inicial del campo
            $prefix . 'initial_amount' => array(
                'name'       => 'Cantidad inicial',
                'desc'       => 'Valor inicial que aparece en el campo.',
                'type'       => 'text',
                'attributes' => array( 'type' => 'number', 'min' => '0', 'step' => '1' ),
            ),

            // Cantidad máxima
            $prefix . 'max_amount' => array(
                'name'       => 'Cantidad máxima',
                'desc'       => 'Máximo de puntos aplicables. 0 = sin límite.',
                'type'       => 'text',
                'attributes' => array( 'type' => 'number', 'min' => '0', 'step' => '1' ),
                'default'    => '0',
            ),
        )
    );
}
add_action( 'cmb2_admin_init', 'gamipress_llms_pp_meta_boxes' );
