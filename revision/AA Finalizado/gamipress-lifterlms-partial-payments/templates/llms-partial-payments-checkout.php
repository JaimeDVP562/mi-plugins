<?php
/**
 * Archivo: templates/llms-partial-payments-checkout.php
 * Este archivo puede ser sobreescrito por el tema activo copiándolo a:
 * yourtheme/gamipress/llms-partial-payments/llms-partial-payments-checkout.php
 */
global $gamipress_llms_pp_template_args;
$a = $gamipress_llms_pp_template_args;  // Acceso corto a los argumentos

$user_id = get_current_user_id();

// Si solo hay un tipo de puntos, mostramos su nombre. Si hay varios, mostramos 'puntos'
$points_label = ( count( $a['points_types'] ) === 1
    ? $a['initial_points_type_data']['plural_name']
    : 'puntos' );
?>

<div id="gamipress-llms-pp" class="llms-form-field-group">

    <!-- Toggle: enlace para mostrar/ocultar el formulario -->
    <div class="gamipress-llms-pp-toggle">
        <p class="llms-notice">
            <?php echo sprintf(
                '¿Usar %s para un descuento? <a href="#">Haz clic aquí</a>',
                strtolower( $points_label )
            ); ?>
        </p>
    </div>

    <!-- Formulario (oculto por defecto, se muestra al hacer clic en el toggle) -->
    <form class="gamipress-llms-pp-form" method="post" style="display:none;">

        <!-- Campo de cantidad de puntos -->
        <div class="llms-form-field">
            <?php foreach ( $a['points_types'] as $points_type => $data ) :
                $style = ( $a['initial_points_type'] !== $points_type ? 'display:none;' : '' ); ?>

            <label style="<?php echo $style; ?>"><?php echo 'Cantidad:'; ?></label>

            <!-- El input donde el usuario escribe cuántos puntos quiere usar -->
            <input
                type="<?php echo $data['field_type']; ?>"
                name="<?php echo $points_type; ?>_points"
                class="gamipress-llms-pp-points"
                placeholder="0"
                step="<?php echo $data['field_step']; ?>"
                min="<?php echo $data['field_min']; ?>"
                max="<?php echo $data['field_max']; ?>"
                value="<?php echo $data['field_value']; ?>"
                style="<?php echo $style; ?>"
            />

            <!-- Saldo actual del usuario -->
            <small style="<?php echo $style; ?>">
                <?php echo sprintf(
                    'Tienes %s en tu saldo.',
                    gamipress_format_points( $data['user_points'], $points_type )
                ); ?>
            </small>

            <?php endforeach; ?>
        </div>

        <!-- Selector de tipo de puntos (si hay más de uno) -->
        <div class="llms-form-field">
            <label>Tipo:</label>
            <?php if ( count( $a['points_types'] ) === 1 ) : ?>
                <input type="hidden" name="points_type" value="<?php echo $a['initial_points_type']; ?>">
                <span><?php echo $a['initial_points_type_data']['plural_name']; ?></span>
            <?php else : ?>
                <select name="points_type" class="gamipress-llms-pp-points-type">
                    <?php foreach ( $a['points_types'] as $pt => $data ) : ?>
                        <option value="<?php echo $pt; ?>"><?php echo $data['plural_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <!-- Preview: muestra en tiempo real cuánto descuento equivale -->
        <div class="llms-form-field">
            <p class="gamipress-llms-pp-preview">
                <?php echo sprintf(
                    'Usarás %s para un descuento de %s.',
                    $a['points_preview'],
                    $a['money_preview']
                ); ?>
            </p>
        </div>

        <!-- Botón para aplicar el descuento -->
        <div class="llms-form-field">
            <button type="submit" class="llms-button-action" id="gamipress-llms-pp-btn">
                Aplicar descuento
            </button>
        </div>

    </form>

    <!-- Aquí aparecerán los mensajes de error o confirmación -->
    <div class="gamipress-llms-pp-notices"></div>

</div>
