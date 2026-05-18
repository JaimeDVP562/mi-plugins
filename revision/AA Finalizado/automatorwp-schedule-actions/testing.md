# AutomatorWP – Schedule Actions (add-on)
## Testing / Verification

### Requisitos
- WordPress
- AutomatorWP activo
- AutomatorWP - Schedule Actions activo
- WP Mail Logging activo

---

# Test 1. Modificar el log (Fecha y hora/Scheduled)✅

## 1) Crear una automatización
1. Ir a AutomatorWP → Automations
2. Click en "Add New Automation"
3. Asignar nombre (ej: "Prueba accion con retardo")

## 2) Añadir el disparador/trigger y la acción/action 
1. En "Disparadores", añadir un trigger (ej. "El usuario accede 1 vez" de WordPress)
2. En "Acciones", añadir un action y añadir Retardo/Delay (ej. "Enviar un correo electrónico a usuario" de AutomatorWP)
3. Guardar cambios

## 3) Ejecutar el evento
1. Cerrar sessión de WordPress
2. Entrar nuevamente en WordPress y acceder a la página

## 4) Modificar el log
1. Entrar al log de AutomatorWP
2. Verificar que se ha creado el log correspondiente
3. Pulsar la opción Ejecutar ahora/Run now

## 5) Verificar la acción
1. Entrar al log de AutomatorWP
2. Verificar que las opciones para modificar el retraso de la acción hayan desaparecido del log
2. Entrar en WP Mail Logging
3. Verificar que el correo electrónico fue enviado al usuario
4. Entrar en phpmyadmin y buscar la tabla wp_options
5. Verificar que se haya creado el hook automatorwp_schedule_actions_execute_action con el nuevo timestamp

### Tener en cuenta el desface horario: WordPress guarda todas las fechas en la base de datos en formato UTC (GMT 0)

---

# Test 2. Modificar el log (Ejecutar ahora/Run now)✅

## 1) Crear una automatización
1. Ir a AutomatorWP → Automations
2. Click en "Add New Automation"
3. Asignar nombre (ej: "Prueba accion con retardo")

## 2) Añadir el disparador/trigger y la acción/action 
1. En "Disparadores", añadir un trigger (ej. "El usuario accede 1 vez" de WordPress)
2. En "Acciones", añadir un action y añadir Retardo/Delay (ej. "Enviar un correo electrónico a usuario" de AutomatorWP)
3. Guardar cambios

## 3) Ejecutar el evento
1. Cerrar sessión de WordPress
2. Entrar nuevamente en WordPress y acceder a la página

## 4) Modificar el log
1. Entrar al log de AutomatorWP
2. Verificar que se ha creado el log correspondiente
3. Pulsar la opción Ejecutar ahora/Run now

## 5) Verificar la acción
1. Entrar al log de AutomatorWP
2. Verificar que las opciones para modificar el retraso de la acción hayan desaparecido del log
2. Entrar en WP Mail Logging
3. Verificar que el correo electrónico fue enviado al usuario
4. Entrar en phpmyadmin, buscar la tabla wp_options y fila cron
5. Verificar que se haya creado el hook automatorwp_schedule_actions_execute_action con el nuevo timestamp

### Tener en cuenta el desface horario: WordPress guarda todas las fechas en la base de datos en formato UTC (GMT 0)

---

# Test 3. Borrado de Usuario (Limpieza de "Procesos huérfanos")✅

## 1) Crear usuario de pruebas con perfil de suscriptor

## 2) Usar automatización anterior o crear una nueva

## 3) Ejecutar el evento

## 4) Verificar el log AutomatorWP del nuevo usuario

## 5) Borrar el nuevo usuario
1. Seleccionar "Borrar todo el contenido", si WP lo pide y confirma el borrado

## 6) Verificar borrado de la acción con retardo
1. Entrar al log de AutomatorWP
2. Verificar que la acción haya desaparecido del log

---

# Test 4. Migración de Versión (Upgrades)✅

## 1) Crear el escenario antiguo
1. Entrar en phpMyAdmin
2. Buscar la tabla wp_automatorwp_actions_meta 
3. Insertar una fila manualmente:
- meta_key: Escribe schedule_action_amount
- meta_value: Escribe 10 (o cualquier número)
- id: Pon el ID de cualquier acción que ya tengas creada

## 2) Simular versión del plugin
1. En phpMyAdmin, Ir a la tabla wp_options
2. Buscar en la columna option_name el nombre: automatorwp_schedule_actions_version
3. Cambiar su option_value de 1.1.8 a 1.0.0.

## 3) Disparar la migración
1. Ir al escritorio de WordPress y refrescar la página

## 4) Verificar
1. Ir a phpMyAdmin, a la tabla wp_automatorwp_actions_meta
2. Buscar la fila insertada en el paso 1
3. Verificar que el meta_key schedule_action_amount ahora ha cambiado a delay_action_amount
4. Ir a la tabla wp_options y busca automatorwp_schedule_actions_version. Ahora debe marcar de nuevo 1.1.8.
