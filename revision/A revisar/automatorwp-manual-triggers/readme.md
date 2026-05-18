# AutomatorWP - Manual Triggers

Plugin de integración para AutomatorWP que permite lanzar automatizaciones manualmente desde código, shortcodes o el panel de administración.

Buena suerte a la persona que le toque revisar este código. :)

### 1. Manual launch (logged-in)
- **Clase:** [AutomatorWP_Manual_Triggers_Manual_Launch](file:///c:/Users/ashin/Desktop/GitLab/practicas-web/Paula%20Lei%20Gimeno/automatorwp-manual-triggers/includes/triggers/manual-launch.php#13-181)
- **Tipo:** `manual_triggers_manual_launch`
- Requiere **user ID** (si no se proporciona, usa el usuario actual)

### 2. Manual launch (anonymous)
- **Clase:** [AutomatorWP_Manual_Triggers_Anonymous_Manual_Launch](file:///c:/Users/ashin/Desktop/GitLab/practicas-web/Paula%20Lei%20Gimeno/automatorwp-manual-triggers/includes/triggers/anonymous-manual-launch.php#13-117)  
- **Tipo:** `manual_triggers_anonymous_manual_launch`
- **No** requiere user ID

## 3 Formas de Lanzar (en ambos triggers)

### ▶ Run Now (panel admin)
- Campo de User ID (solo en logged-in, vacío = usuario admin actual)
- Botón "Run Now" que ejecuta el trigger vía AJAX

### 📝 Code Example (desplegable)

**Logged-in:**
```php
// Uso básico (usa el usuario actual)
automatorwp_run_trigger( {ID} );

// Con user ID específico
automatorwp_run_trigger( {ID}, 123 );
```

**Anonymous:**
```php
// No necesita user ID
automatorwp_run_trigger( {ID} );
```

### 🔗 Shortcode

**Logged-in:**
```
[automatorwp_manual_trigger trigger="{ID}" user="" label="Run"]       ← usuario actual
[automatorwp_manual_trigger trigger="{ID}" user="123" label="Run"]    ← usuario específico
```

**Anonymous:**
```
[automatorwp_manual_trigger trigger="{ID}" label="Run"]
```

> [!NOTE]
> El `{ID}` se reemplaza automáticamente por el ID real del trigger en el panel de edición.

## Shortcode Attributes

| Atributo  | Tipo    | Default   | Descripción |
|-----------|---------|-----------|-------------|
| trigger (includes/triggers/manual-launch.php) | int     | 0         | **Requerido.** ID del trigger |
| user (includes/triggers/manual-launch.php)    | string  | `""`      | User ID. `""` = usuario actual, `"123"` = usuario específico |
| label   | string  | `"Run"`   | Texto del botón/enlace |
| type    | string  | `"button"`| `"button"` o `"link"` |
| class   | string  | `""`      | Clases CSS adicionales |
