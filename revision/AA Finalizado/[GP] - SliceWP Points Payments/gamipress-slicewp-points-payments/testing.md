# GamiPress - SliceWP Points Payments
## Testing / Verification

### Requisitos Previos
- Entorno: XAMPP con Apache y PHP 7.4+.
- WordPress (última versión).
- Plugins Activos:
    - GamiPress.
    - SliceWP.
    - GamiPress - SliceWP Points Payments.

#### Configuración inicial: - Tener al menos un "Points Type" creado en GamiPress (slug: points).
- Tener al menos dos tipos de puntos creados en GamiPress.
- Tener al menos dos afiliados registrados en SliceWP y vinculados con sus usuarios de WordPress.
- Métodos de Pago:
    - Afiliado A: Configurado con el método correspondiente a points.
    - Afiliado B: Configurado con el método correspondiente a gems.
- Tener al menos una Comisión pendiente de pago para cada tipo de puntos.

---

## 1) Validación de UI e integración dinámica ✅
Objetivo: Verificar que el Add-on registra automáticamente un método por cada tipo de puntos.
1. Ir a SliceWP > Affiliates > editar el afiliado.
2. Desplegar el campo Payout Method.
3. Verificación:
- Deben aparecer todos los tipos de puntos de GamiPress como opciones individuales.
- El label debe coincidir con el plural_name (ej: "Puntos", "Gemas").
- Los métodos internos deben tener el prefijo gamipress-{slug}.
4. Seleccionar un método basado en puntos para cada usuario y guardar.

## 2) Prueba de Pago Individual
### A) Ejecución del Pago ✅
Objetivo: Validar que el sistema identifica el tipo de punto correcto desde el pago, no desde un ajuste global.
1. Ir a SliceWP > Payouts y generar un payout que contenga pagos de distintos tipos de puntos.
2. Localizar un pago específico (ej: del método vinculado a gemas).
3. Procesar el pago individualmente.
4. Verificación:
- Saldo: Solo debe aumentar el saldo de "Gemas" del usuario, no el de "Puntos".
- Metadatos: El payment debe tener el meta _gamipress_slicewp_points_paid = 1 (usando slicewp_update_payment_meta).
- Sincronización: Tanto el pago como las comisiones deben figurar como "Paid" en SliceWP.

### B) Registro en Logs de GamiPress ✅
1. Ir a GamiPress > Logs.
2. Verificación: La entrada debe especificar el tipo de punto exacto otorgado, confirmando que la función gamipress_slicewp_points_payments_get_points_type_from_method funcionó correctamente.

## 3) Prueba de Pago Masivo (Bulk Payout) ✅
Objetivo: Validar que el filtro payout_method evita procesar pagos incorrectos en ejecuciones masivas.
1. Generar un Payout masivo que incluya:
- 3 pagos de "Puntos".
- 2 pagos de "Gemas".
2. En la vista del Payout, seleccionar en el selector de bulk: "Puntos".
3. Hacer clic en "Pay Affiliates".
4. Verificación:
- Solo los 3 pagos de "Puntos" deben pasar a "Paid".
- Los 2 pagos de "Gemas" deben permanecer "Unpaid" (gracias al filtro de current_filter() en do_bulk_payments).
- Repetir el proceso para "Gemas" y verificar que se completen correctamente.

## 4) Pruebas de Casos Críticos (Edge Cases)
### A) Confirmación de Tipos Inexistentes ✅
Objetivo: Evitar fallos si se intenta pagar con un tipo de punto que fue borrado de GamiPress.
1. Intentar procesar un pago cuyo payout_method sea gamipress-puntos-borrados.
2. Resultado esperado: El plugin detecta que el tipo no existe mediante gamipress_get_points_type(), cancela la operación y el pago permanece "Unpaid" en SliceWP sin generar errores fatales.

### B) Verificación de Ratio de Conversión✅
Objetivo: Validar que el filtro de conversión ahora acepta el parámetro $points_type.
1. Añadir el siguiente filtro en un plugin de prueba:
```php
add_filter( 'gamipress_slicewp_points_payments_convert_amount_to_points', function( $points, $amount, $points_type ) {
    if ( 'gemas' === $points_type ) return $points * 10; // 1€ = 10 gemas
    if ( 'puntos' === $points_type ) return $points * 5; // 1€ = 5 puntos
    return $points;
}, 10, 3 );
```
2. Procesar pagos de 10€ para ambos tipos.
3. Verificación: El usuario debe recibir 100 gemas y 50 puntos respectivamente.

### C) Seguridad de Metadatos (SliceWP Tables) ✅
Objetivo: Confirmar que los datos se guardan en la tabla de SliceWP y no en wp_postmeta.
1. Tras un pago, revisar la tabla wp_slicewp_paymentmeta (o la correspondiente según prefijo).
2. Verificación: El meta _gamipress_slicewp_points_paid debe existir allí. No debe haber rastro en wp_postmeta.

## 5) Resultados Obtenidos
1.  Registro dinámico de todos los tipos de puntos [✅] Pasa / [ ] Falla
2.  Resolución automática del tipo desde el pago [✅] Pasa / [ ] Falla
3.  Aislamiento de pagos en procesos Bulk [✅] Pasa / [ ] Falla
4.  Conversión consciente del tipo de punto [✅] Pasa / [ ] Falla
5.  Actualización de comisiones vinculadas [✅] Pasa / [ ] Falla
6.  Prevención de pagos duplicados [✅] Pasa / [ ] Falla
7.  Limpieza de Logs (Sin Warnings de "status") [✅] Pasa / [ ] Falla
8.  Persistencia en tablas nativas de SliceWP [✅] Pasa / [ ] Falla
