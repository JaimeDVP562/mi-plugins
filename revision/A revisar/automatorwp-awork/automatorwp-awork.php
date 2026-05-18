<?php
/**
 * Plugin Name:       AutomatorWP - awork Integration
 * Description:       Integración profesional para conectar WordPress con awork. Incluye guía de API comentada.
 * Version:           1.6.0
 * Author:            Tu Nombre
 * Text Domain:       automatorwp-awork
 */

// 1. SEGURIDAD: Evitar acceso directo al archivo 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REGISTRO DE LA INTEGRACIÓN
 */
function mwp_awork_register_integration() {
	
	if ( ! function_exists( 'automatorwp_register_integration' ) ) {
		return;
	}

	automatorwp_register_integration( 'awork', array(
		'label' => 'awork',
		'icon'  => 'https://www.awork.com/favicon.ico',
	) );
}
add_action( 'automatorwp_init', 'mwp_awork_register_integration' );

/**
 * REGISTRO DE LA ACCIÓN
 * El "Por Qué": Añadimos 'title' y 'button_label' para robustecer la interfaz.
 */
function mwp_awork_register_action() {

	if ( ! function_exists( 'automatorwp_register_action' ) ) {
		return;
	}

	automatorwp_register_action( 'awork_create_project', array(
		'integration'  => 'awork',
		'label'        => 'Crear un proyecto en awork',
		'title'        => 'Configuración de Proyecto awork',
		'sentence'     => 'Crear un proyecto en awork con el nombre {project_name}',
		'button_label' => 'Confirmar Acción',
		'description'  => 'Crea un nuevo proyecto en tu espacio de trabajo de awork.',
		'options'      => array(
			array(
				'type'        => 'text',
				'name'        => 'project_name',
				'label'       => 'Nombre del Proyecto',
				'placeholder' => 'Ej: Mi nuevo proyecto de WordPress',
				'required'    => true,
				'description' => 'Introduce el nombre del proyecto para la API de awork.',
			),
		),
	) );
}
add_action( 'automatorwp_init', 'mwp_awork_register_action' );

/**
 * LÓGICA DE EJECUCIÓN
 * Incluye la estructura de la API comentada para revisión del tutor.
 */
function mwp_awork_execution_test( $user_id, $action_id, $recipe_id, $args ) {

	if ( ! function_exists( 'automatorwp_get_option' ) ) {
		return;
	}

	$project_name = automatorwp_get_option( $action_id, 'project_name', 'Proyecto sin nombre' );
	$user_info    = get_userdata( $user_id );
	$user_name    = ( false !== $user_info ) ? $user_info->display_name : 'Usuario Desconocido'; // [cite: 116, 724]

	/**
	 * PARTE IMPORTANTE (CÓDIGO DE INTEGRACIÓN COMENTADO)
	 * Descomentar y rellenar cuando se obtenga la API Key.
	 * * $api_key = 'TU_TOKEN_AQUÍ';
	 * $url     = 'https://api.awork.com/api/v1/projects';
	 * * $response = wp_remote_post( $url, array(
	 * 'headers' => array(
	 * 'Authorization' => 'Bearer ' . $api_key,
	 * 'Content-Type'  => 'application/json',
	 * ),
	 * 'body'    => wp_json_encode( array( 'name' => $project_name ) ),
	 * ) );
	 */

	error_log( '--- SIMULACIÓN AWORK ---' );
	error_log( 'ACCIÓN: Intento de crear proyecto: ' . $project_name );
	error_log( 'USUARIO: ' . $user_name );
	error_log( '--- FIN SIMULACIÓN ---' );
}

/**
 * Hook de ejecución con espaciado correcto según estándares [cite: 207, 248]
 */
add_action( 'automatorwp_awork_awork_create_project_execution', 'mwp_awork_execution_test', 10, 4 );