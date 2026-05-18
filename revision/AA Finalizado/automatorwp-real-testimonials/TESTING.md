# AutomatorWP – Real Testimonials
## Testing / Verification

### Requisitos Previos
- XAMPP activo con Apache y PHP 7.4 o superior.
- WordPress instalado localmente.
- Plugin AutomatorWP activo.
- Plugin WP Real Testimonials activo.
- Herramienta de Debug: Acceso a wp-content/debug.log o un visor de logs.

---

## 1) Validación de UI e Integración (Settings) ✅
Objetivo: Verificar que AutomatorWP reconoce la integración.

1. Ir a AutomatorWP > Integrations.
2. Buscar en el listado "WP Real Testimonials".
3. Verificación:
 - El logo (No hay fichero svg y el código está comentado) ❌
 - El nombre deben aparecer correctamente en la lista de integraciones disponibles.

## 2) Pruebas del Trigger (User submits a testimonial)
### A) Configuración de la Automatización ✅
Objetivo: Validar que el trigger se puede configurar y que los selectores AJAX funcionan.
1. Crear una nueva automatización.
2. Añadir Trigger: WP Real Testimonials > User submits a testimonial.
3. UI Check (AJAX):
 - En el selector "Testimonial:", escribir las primeras letras de un testimonio existente.
 - Resultado esperado: El selector debe mostrar una lista desplegable con los títulos de los testimonios existentes. ✅
4. Seleccionar "any testimonial" para la primera prueba.
5. Añadir una acción simple (ej. "Añadir un tag al usuario" o "Enviar un email").

### B) Ejecución y Registro ✅
Objetivo: Validar que al crear un testimonio manualmente el trigger se dispara.
1. Ir a Testimonials > Add New.
2. Rellenar título, contenido y campos personalizados (si existen).
3. Publicar el testimonio.
4. Verificación: En WP Admin: Ir a AutomatorWP > Logs. Debe aparecer una entrada nueva indicando que el usuario completó el trigger.

### C) Validación de Tags ✅
Objetivo: Comprobar que los campos del testimonio se capturan y pueden usarse en otras acciones.
1. En la acción de la automatización (ej. un email), intentar usar el tag: .{t:[ID]:testimonial_field:testimonial_title}
2. Ejecutar el trigger de nuevo.
3. Verificación: El valor sustituido debe coincidir con el título del testimonio creado (valida automatorwp_realtestimonial_parse_automation_tags).
4. Pruebas de Casos Críticos
 - Filtro por Testimonio Específico:
    - Configurar el trigger para que solo salte con un testimonio concreto "A".
    - Crear un testimonio "B".
    - Resultado esperado: El log no debe registrar ninguna actividad (valida user_deserves_trigger).
 - Usuario no Identificado:
    - Intentar enviar un testimonio (si el formulario es frontal) sin estar logueado.
    - Resultado esperado: El trigger debe ignorar la acción (valida la comprobación user_id === 0 en el listener).
 - Evitar Duplicados en Edición:
    - Editar un testimonio ya existente y darle a "Actualizar".
    - Resultado esperado: No debe dispararse la automatización de nuevo (valida el check $update en el listener).
5. Solución de Problemas (Logs)
Si la automatización no se dispara, monitoriza Debug Log: En wp-content/debug.log, busca errores.

## 3)Resultados Obtenidos
1. Conexión AJAX: [✅] Pasa / [ ] Falla
2. Disparo de Trigger: [✅] Pasa / [ ] Falla
3. Captura de Metafields: [✅] Pasa / [ ] Falla