# Guía para Probar el Plugin GamiPress Recurring Rewards

## Requisitos Previos

- Un sitio de WordPress instalado y funcionando (por ejemplo, usando XAMPP).
- El plugin principal **GamiPress** instalado y activado.
- El plugin **GamiPress Recurring Rewards** instalado en `wp-content/plugins/gamipress-recurring-rewards/`.

## Pasos para Instalar y Activar

1. **Instalar el Plugin:**
   - Copia la carpeta `gamipress-recurring-rewards` al directorio `wp-content/plugins/` de tu instalación de WordPress.

2. **Activar el Plugin:**
   - Ve al menú de plugins de WordPress.
   - Busca **"GamiPress Recurring Rewards"**.
   - Haz clic en **Activar**.

## Pruebas Funcionales

### Prueba de Recurring Rewards

1. En GamiPress, crea un tipo de puntos y un logro o un rango.

2. Asígnaselo al usuario directamente o añádele un step, para que obtenga dicho logro o rango.

3. Ve a **GamiPress > Recurring Rewards**.

4. Crea un nuevo Recurring Reward y configura los distintos campos configurables.

5. Una vez creado, ve a los usuarios y, como podrás observar, se irán sumando los puntos a los usuarios que hayan cumplido el requisito (esto, claro está, si ya ha pasado la fecha). Nota: Si no se suman los puntos, desactiva los plugins que no se estén utilizando para probar este plugin.

6. Si entras de nuevo en **GamiPress > Recurring Rewards**, podrás observar los distintos Recurring Rewards que has creado.

7. Si entras en el modo edición de dicho Recurring Reward, podrás observar qué usuarios tienen acceso.

8. Además, se deben haber creado cuatro tablas llamadas `wp_gamipress_recurring_rewards`, `wp_gamipress_recurring_rewards_meta`, `wp_gamipress_recurring_reward_users`, `wp_gamipress_recurring_reward_users_meta`. Comprueba que se han rellenado correctamente.
