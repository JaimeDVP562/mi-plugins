# Tag {form_value_displayed} - Documentación Completa

## Índice
1. [Descripción General](#descripción-general)
2. [Instalación y Configuración](#instalación-y-configuración)
3. [Sintaxis y Uso](#sintaxis-y-uso)
4. [Ejemplos Prácticos](#ejemplos-prácticos)
5. [Integraciones Soportadas](#integraciones-soportadas)
6. [Resolución de Problemas](#resolución-de-problemas)

---

## Descripción General

### ¿Qué es la tag `form_value_displayed`?

Es una nueva tag de AutomatorWP que permite obtener el **valor mostrado (label)** de un campo de formulario en lugar de su **valor interno** (value).

### ¿Por qué es necesaria?

En los formularios, los campos con opciones (checkboxes, radios, selects) tienen dos valores:
- **Valor interno**: Lo que se envía/procesa (ej: "1", "op_a", "es")
- **Valor mostrado**: Lo que ve el usuario (ej: "Sí", "Opción A", "Español")

Con la tag `form_value_displayed`, puedes usar el label en lugar del valor interno.

### Ejemplo de Diferencia

```
Checkbox: "¿Acepta los términos?"
  - Valor interno: "1"
  - Valor mostrado: "Acepta los términos"
  
Radio: Selecciona tu país
  - Valor interno: "es"
  - Valor mostrado: "España"
  
Select: Idioma preferido
  - Valor interno: "en"
  - Valor mostrado: "English"
```

---

## Instalación y Configuración

### Para BBForms

1. Abre: `TAREA BBFORMS/bbforms/includes/tags.php`

2. En la función `bbforms_get_tags()`, busca la sección de tags del formulario (alrededor de la línea 42):
```php
$tags['form']['tags']['form.field:FIELD_NAME'] = array(
    'label'     => __( 'Field value', 'bbforms' ),
    // ...
);
```

3. Agrega justo después:
```php
$tags['form']['tags']['form.field_displayed:FIELD_NAME'] = array(
    'label'     => __( 'Field displayed value', 'bbforms' ),
    'type'      => 'text',
    'preview'   => __( 'Field displayed value (label), replace "FIELD_NAME" by the field name. For choice fields (radio, checkbox, select), returns the label instead of the value.', 'bbforms' ),
);
```

4. En la función `bbforms_get_tag_replacement()`, en la sección de "form tags", agrega antes del cierre de la sección:
```php
// form.field_displayed:FIELD_NAME tag - Get the displayed label value
if( bbforms_starts_with( $tag_name, 'form.field_displayed:' ) ) {
    
    $field_name = explode(':', $tag_name)[1];
    
    if( isset( $form_fields[$field_name] ) ) {
        $field_value = $form_fields[$field_name];
        $replacement = bbforms_get_field_display_value( $form, $field_name, $field_value );
    }
}
```

5. Copia las funciones auxiliares desde `bbforms-form-value-displayed-snippet.php` a `bbforms/includes/functions.php`:
   - `bbforms_get_field_display_value()`
   - `bbforms_find_option_label()`
   - `bbforms_starts_with()` (si no existe)

### Para Forminator

1. El código completo está en: `forminator-form-value-displayed-snippet.php`

2. Copia todo el código a una nueva integración o agrega al archivo de integraciones existente

3. Asegúrate de que los hooks se registren en `automatorwp_init` con prioridad 20

### Para Gravity Forms

1. El código completo está en: `gravity-forms-form-value-displayed-snippet.php`

2. Copia a una nueva integración o integración existente

3. Verifica que Gravity Forms esté activo (clase `GFFormsModel` disponible)

### Para MetForm

1. Abre: `A revisar/automatorwp-metform/includes/functions.php`

2. Copia el contenido de `metform-form-value-displayed-snippet.php` al final del archivo

3. Asegúrate de que el hook tenga prioridad 11 para procesarse después del principal

---

## Sintaxis y Uso

### Sintaxis General

```
{form_value_displayed:FIELD_ID}
```

### Por Integración

#### BBForms
```
{form.field_displayed:nombre_del_campo}
```

#### Forminator / Gravity Forms / MetForm  
```
{t:TRIGGER_ID:forminator_field_displayed:FIELD_ID}
```
o
```
{TRIGGER_ID:forminator_field_displayed:FIELD_ID}
```

### Parámetros

- **FIELD_ID / FIELD_NAME**: El identificador o nombre del campo en el formulario
- **TRIGGER_ID**: El ID del trigger (solo para PatternAutomatorWP con triggers)

### Tipos de Campos Soportados

**Totalmente Soportados:**
- Checkbox
- Radio
- Select / Dropdown
- Multiselect
- Campos de opciones

**Parcialmente Soportados:**
- List fields (se retorna como valor mostrado)
- Product fields (retorna la etiqueta del producto)

ℹ**No Soportados:**
- Campos de texto
- Email
- Número
- Fecha
- Archivo
- Campos personalizados sin opciones

Para estos campos no soportados, la tag retorna el valor interno.

---

## Estado del Entregable

Este paquete está listo para revisión: la documentación está completa, los snippets están organizados por integración, y el material incluye instrucciones de implementación, pruebas y depuración. El siguiente paso es integrar los snippets en los plugins reales y validar en un entorno WordPress con AutomatorWP y el plugin de formulario correspondiente.

---

## Ejemplos Prácticos

### Ejemplo 1: BBForms - Formulario de Contacto

**Formulario:**
- Campo: checkbox "tipo_contacto"
  - Opción 1: valor="ventas", label="Solicitud de ventas"
  - Opción 2: valor="soporte", label="Solicitud de soporte"
  - Opción 3: valor="otro", label="Otro"

**Valor enviado:** "ventas"

**Con tag antigua:**
```
Mensaje: El usuario seleccionó: ventas
```

**Con tag nueva:**
```
{form.field_displayed:tipo_contacto}
= "Solicitud de ventas"
```

**Correo resultante:**
```
Mensaje: El usuario seleccionó: Solicitud de ventas 
```

---

### Ejemplo 2: Forminator - Encuesta

**Formulario:**
- Trigger: Formulario "Encuesta de Satisfacción" enviado
- Trigger ID: 42

**Radio field:**
- nombre_campo: "satisfaccion"
- Valores: 1="Muy insatisfecho", 2="Insatisfecho", 3="Neutral", 4="Satisfecho", 5="Muy satisfecho"

**En tu automación:**

```
Texto a mostrar en la notificación:
"El usuario está: {t:42:forminator_field_displayed:satisfaccion}"

Si usuario selecciona opción "5":
Resultado: "El usuario está: Muy satisfecho" 
```

---

### Ejemplo 3: Gravity Forms - Formulario de Solicitud

**Formulario:**
- Form ID: 3
- Field ID: 5 (dropdown "país")

**En el action (ej: email, webhook, etc):**

```
Email body:
"País seleccionado: {t:23:gf_field_displayed:5}"
(donde 23 es el TRIGGER_ID)

Resultado si usuario selecciona "es":
"País seleccionado: España" 
```

---

### Ejemplo 4: MetForm - Formulario Multi-opciones

**Formulario:**
- Form ID: 1
- Trigger ID: 15

**Multiselect con integreses:**
- Campo: "intereses"
- Opciones: 
  - "desarrollo" = "Desarrollo de Software"
  - "marketing" = "Marketing Digital"
  - "ventas" = "Ventas B2B"

**Usuario selecciona:** desarrollo, marketing

```
Tag: {t:15:metform_field_displayed:intereses}
Resultado: "Desarrollo de Software, Marketing Digital" 
```

---

## Integraciones Soportadas

### Estado Actual

| Integración | Estado | Archivo | Notas |
|-------------|--------|---------|-------|
| **BBForms** | Implementado | `bbforms-form-value-displayed-snippet.php` | Requiere actualización manual en archivos |
| **Forminator** | Implementado | `forminator-form-value-displayed-snippet.php` | Nueva integración o addon |
| **Gravity Forms** |  Implementado | `gravity-forms-form-value-displayed-snippet.php` | Nueva integración o addon |
| **MetForm** |  Implementado | `metform-form-value-displayed-snippet.php` | Extensión a integración existente |

### Extensiones Futuras

Estas integraciones pueden extenderse siguiendo el mismo patrón:
- WPForms
- Fluent Forms
- Elementor Forms
- Ninja Forms
- Caldera Forms
- Quill Forms

---

## Resolución de Problemas

### La tag no se reemplaza (aparece literal)

**Causas posibles:**
1. El código no fue pegado correctamente
2. Las funciones auxiliares no están definidas
3. El trigger no está configurado correctamente

**Solución:**
- Verifica que copies TODAS las funciones auxiliares
- Verifica que el TRIGGER_ID sea correcto
- Asegúrate de usar el patrón correcto para tu integración

### Retorna valor vacío

**Causas posibles:**
1. El campo no existe con ese nombre/ID
2. El campo no tiene opciones configuradas
3. El valor del campo está vacío

**Solución:**
- Verifica el nombre exacto del campo (sensible a mayúsculas)
- Comprueba que el campo tenga opciones configuradas
- Si el campo está vacío, la tag también lo estará

### Retorna el valor interno, no el label

**Causas posibles:**
1. El tipo de campo no es "choice" (radio, checkbox, select, etc)
2. Las opciones no están configuradas correctamente
3. El valor no coincide exactamente con ninguna opción

**Solución:**
- Solo campos con opciones devuelven labels
- Verifica que las opciones estén bien configuradas
- Comprueba que no haya espacios o caracteres especiales en los valores

---

## Preguntas Frecuentes

### ¿Puede usarse en todos los lados del automatorwp?

Sí, la tag puede usarse en cualquier campo que acepte tags de AutomatorWP:
- Notificaciones por email
- Webhooks
- Acciones personalizadas
- Condicionales
- Etc.

### ¿Qué pasa con los campos sin opciones?

La tag devuelve el valor interno (igual que la tag normal `form_value`).

### ¿Y si el usuario selecciona múltiples opciones?

La tag devuelve todas las etiquetas separadas por coma:
```
{form_value_displayed:checkbox_multiple}
= "Opción 1, Opción 2, Opción 3"
```

### ¿Funciona con valores vacios?

Sí, devuelve una cadena vacía si el campo está vacío.

### ¿Se puede combinar tag antigua y nueva?

Sí, puedes usar ambas:
```
Valor interno: {form_field:campo}
Valor mostrado: {form_field_displayed:campo}
```

---

## Archivos Entregados

```
[AWP] Tag para obtener el valor del label en integraciones con forms/
├── README.md                             ← Este archivo (documentación)
├── bbforms-form-value-displayed-snippet.php
├── forminator-form-value-displayed-snippet.php
├── gravity-forms-form-value-displayed-snippet.php
├── metform-form-value-displayed-snippet.php
└── TESTING_Y_DEBUGGING.php                ← Guía de validación
```

---

## Contrato del Código

- **Lenguaje**: PHP 7.0+
- **Dependencias**: AutomatorWP
- **Compatibilidad**: WordPress 5.0+
- **Licencia**: Same as AutomatorWP/Client project

---

## Notas Importantes

1. **Backup**: Realiza backup antes de modificar archivos de integraciones
2. **Pruebas**: Prueba cada integración después de implementar
3. **Performance**: Caching se recomienda si hay muchas llamadas al mismo formulario
4. **Actualizaciones**: Estos cambios pueden verse afectados por actualizaciones de plugins

---

## Soporte Adicional

Si tienes preguntas específicas sobre implementación:
1. Comprueba los ejemplos de snippets para tu integración
2. Revisa `TESTING_Y_DEBUGGING.php` para validar los cambios
3. Verifica el código existente de AutomatorWP para patrones similares

