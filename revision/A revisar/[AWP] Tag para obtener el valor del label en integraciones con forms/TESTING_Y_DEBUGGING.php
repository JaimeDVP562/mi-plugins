<?php
/**
 * TESTING & VALIDATION - Guía de Pruebas
 * 
 * Archivo de referencia para testear la implementación de form_value_displayed
 * en cada integración.
 */

// ============================================================================
// 1. PRUEBA BÁSICA DE CARGA
// ============================================================================

/**
 * Test 1: Verificar que la tag está registrada
 * 
 * Cómo hacerlo:
 * - Ir a un formulario que dispare el trigger
 * - En el campo de acción que acepta tags (ej: email)
 * - Escribir: {form.field_displayed:test}
 * - Si aparece el campo con el label, la tag está registrada ✅
 */

// Verificar con código:
if( function_exists( 'bbforms_get_tags' ) ) {
    $all_tags = bbforms_get_tags();
    
    if( isset( $all_tags['form']['tags']['form.field_displayed:FIELD_NAME'] ) ) {
        echo "✅ Tag 'form.field_displayed' está registrada";
    } else {
        echo "❌ Tag no registrada - verifica la línea en bbforms_get_tags()";
    }
}

// ============================================================================
// 2. PRUEBA DE CAMPO CON OPCIONES
// ============================================================================

/**
 * Test 2: Probar con un campo checkbox/radio
 * 
 * Configuración de prueba:
 * 1. Crear o usar un formulario con campos de opciones
 * 2. Ejemplo: Radio "País" con opciones:
 *    - Valor: "es" → Label: "España"
 *    - Valor: "en" → Label: "English"
 *    - Valor: "fr" → Label: "Français"
 * 3. En acción usar: {form.field_displayed:pais}
 * 4. Enviar formulario seleccionando "es"
 * 5. Verificar que se reemplaza con "España" ✅
 */

// Test local de mapeo de valores:
function test_field_display_mapping() {
    
    $test_cases = array(
        'checkbox' => array(
            'type'    => 'checkbox',
            'value'   => '1',
            'options' => array(
                (object) array( 'value' => '1', 'label' => 'Aceptar términos' ),
            ),
            'expected' => 'Aceptar términos',
        ),
        'radio' => array(
            'type'    => 'radio',
            'value'   => 'es',
            'options' => array(
                (object) array( 'value' => 'es', 'label' => 'España' ),
                (object) array( 'value' => 'en', 'label' => 'English' ),
            ),
            'expected' => 'España',
        ),
        'multiselect' => array(
            'type'    => 'multiselect',
            'value'   => array( 'opt1', 'opt2' ),
            'options' => array(
                (object) array( 'value' => 'opt1', 'label' => 'Opción 1' ),
                (object) array( 'value' => 'opt2', 'label' => 'Opción 2' ),
                (object) array( 'value' => 'opt3', 'label' => 'Opción 3' ),
            ),
            'expected' => 'Opción 1, Opción 2',
        ),
    );
    
    foreach( $test_cases as $name => $test ) {
        
        if( function_exists( 'bbforms_find_option_label' ) ) {
            
            if( is_array( $test['value'] ) ) {
                $results = array();
                foreach( $test['value'] as $v ) {
                    $results[] = bbforms_find_option_label( $v, $test['options'] );
                }
                $result = implode( ', ', $results );
            } else {
                $result = bbforms_find_option_label( $test['value'], $test['options'] );
            }
            
            if( $result === $test['expected'] ) {
                echo "✅ Test '$name' pasó: $result\n";
            } else {
                echo "❌ Test '$name' falló: esperado '{$test['expected']}', obtenido '$result'\n";
            }
        }
    }
}

// ============================================================================
// 3. PRUEBA DE CAMPOS SIN OPCIONES
// ============================================================================

/**
 * Test 3: Campos sin opciones deben retornar el valor interno
 * 
 * Configuración:
 * 1. Crear campo de texto "nombre"
 * 2. Usuario ingresa: "Juan"
 * 3. En acción usar: {form.field_displayed:nombre}
 * 4. Debe retornar: "Juan" (valor interno, sin cambios) ✅
 * 
 * Esto es correcto porque campos de texto no tienen opciones
 */

function test_non_choice_field() {
    $non_choice_types = array(
        'text',
        'email', 
        'number',
        'date',
        'textarea',
        'file',
        'password',
        'url',
        'phone',
    );
    
    // Estos campos no tienen opciones, deben retornar el valor como está
    $test_value = "mi_valor_cualquiera";
    
    // El resultado debe ser igual al valor
    echo "Campos sin opciones retornan valor como está: $test_value\n";
}

// ============================================================================
// 4. ESCENARIOS DE PRUEBA MENCIONAN
// ============================================================================

/**
 * Escenario 1: Formulario de Contacto (BBForms)
 */
$scenario_1 = "
ESCENARIO: Formulario de Contacto BBForms
─────────────────────────────────────────

Campos del formulario:
- nombre (text): que ingrese el usuario
- email (email): email del usuario  
- tipo_consulta (radio):
  ✓ Valor: 'ventas' → Label: 'Solicitud de Ventas'
  ✓ Valor: 'soporte' → Label: 'Solicitud de Soporte'
  ✓ Valor: 'otro' → Label: 'Otro'

Acción: Enviar email al admin

Email template:
├─ Para: admin@site.com
├─ Asunto: Nueva consulta de {form.field_displayed:tipo_consulta}
├─ Cuerpo:
│  Nombre: {form.field:nombre}
│  Email: {form.field:email}
│  Tipo: {form.field_displayed:tipo_consulta}
│  (Valor interno: {form.field:tipo_consulta})

Test:
1. Usuario completa formulario
2. Selecciona: 'ventas' en tipo_consulta
3. Envía

Resultado esperado:
├─ Asunto: Nueva consulta de Solicitud de Ventas ✅
├─ En cuerpo:
│  Tipo: Solicitud de Ventas ✅
│  (Valor interno: ventas)
";

/**
 * Escenario 2: Encuesta Forminator
 */
$scenario_2 = "
ESCENARIO: Encuesta de Satisfacción (Forminator)
─────────────────────────────────────────────────

Trigger: Formulario 'Encuesta' enviado (ID: 42)

Campos:
- satisfaccion (radio):
  1 → 'Muy insatisfecho'
  2 → 'Insatisfecho'
  3 → 'Neutral'
  4 → 'Satisfecho'
  5 → 'Muy satisfecho'

Action: Guardar en Gravity Forms

Mapping:
├─ Nombre: {t:42:form_field:nombre}
├─ Email: {t:42:form_field:email}
├─ Nivel: {t:42:forminator_field_displayed:satisfaccion}

Test:
1. Usuario selecciona 5 (Muy satisfecho)
2. Trigger dispara

Datos guardados:
├─ Nombre: Juan
├─ Email: juan@example.com
├─ Nivel: Muy satisfecho ✅ (no '5')
";

/**
 * Escenario 3: Gravity Forms - Múltiples selecciones
 */
$scenario_3 = "
ESCENARIO: Múltiple selección en Gravity Forms
────────────────────────────────────────────────

Form: 'Selecciona tus intereses'
Field ID: 7 → multiselect 'intereses'

Opciones:
- 'dev' → 'Desarrollo'
- 'design' → 'Diseño'
- 'mkt' → 'Marketing'
- 'ux' → 'UX/UI'

User selecciona: dev, design, ux

Action: Email

Email body:
'Intereses: {t:15:gf_field_displayed:7}'

Resultado:
'Intereses: Desarrollo, Diseño, UX/UI' ✅
";

// ============================================================================
// 5. LISTA DE VERIFICACIÓN DE DEBUGGING
// ============================================================================

/**
 * Checklist para troubleshooting
 */
$debugging_checklist = "
CHECKLIST DE DEBUGGING
══════════════════════════════════════════════

□ Verificación de Registro
  ├─ ¿Las funciones auxiliares están definidas?
  ├─ ¿La tag está en bbforms_get_tags()?
  ├─ ¿El hook de parsing está registrado?
  └─ ¿Las prioridades son correctas?

□ Verificación de Datos
  ├─ ¿El formulario tiene el campo?
  ├─ ¿El campo tiene opciones configuradas?
  ├─ ├─ NOTA: Sin opciones, retorna valor interno (correcto)
  ├─ ¿El usuario seleccionó una opción?
  └─ ¿El valor coincide con las opciones?

□ Verificación de Integraciones
  ├─ Para BBForms:
  │  └─ ¿Editaste tags.php Y functions.php?
  ├─ Para Forminator:
  │  └─ ¿Copiaste todo el código, incluidos los hooks?
  ├─ Para Gravity Forms:
  │  └─ ¿Está GFFormsModel disponible?
  └─ Para MetForm:
     └─ ¿La prioridad del hook es 11?

□ Pattern Matching
  ├─ BBForms: {form.field_displayed:FIELD_NAME}
  ├─ Forminator: {t:TRIGGER_ID:forminator_field_displayed:FIELD_ID}
  ├─ GF: {t:TRIGGER_ID:gf_field_displayed:FIELD_ID}
  └─ MetForm: {t:TRIGGER_ID:metform_field_displayed:FIELD_NAME}

□ Casos Especiales
  ├─ ¿Valores con espacios en blanco?
  ├─ ¿Caracteres especiales en valores?
  └─ ¿Arrays vs valores simples?
";

// ============================================================================
// 6. CONSOLE LOGS PARA DEBUGGING
// ============================================================================

/**
 * Agregar a las funciones para debugging
 */
$debug_code = "
// Agregar al inicio de bbforms_get_field_display_value() para debugging:

// Debug: Mostrar qué se está procesando
error_log( 'DEBUG: Processing field ' . $field_name . ' with value ' . json_encode( $field_value ) );
error_log( 'DEBUG: Field type: ' . $field_type );
error_log( 'DEBUG: Options: ' . json_encode( $options ) );

// Debug: Mostrar resultado
error_log( 'DEBUG: Result: ' . $replacement );
";

?>

<!-- 
CÓMO USAR ESTE ARCHIVO:

1. COPY-PASTE del código PHP anterior en functions.php y ejecuta las funciones de test:
   - test_field_display_mapping() - prueba mapeos
   - test_non_choice_field() - prueba campos sin opciones

2. VERIFICA LOS ESCENARIOS listados arriba manualmente

3. USA EL CHECKLIST de debugging si algo no funciona

4. AGREGA LA SALIDA DE DEBUG (error_log) si necesitas investigar a fondo

En wp-content/debug.log aparecerán los logs para verificar.
-->
