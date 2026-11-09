# Taller Electromecánico Parra — Versión 2

## Actualizar una instalación existente (conservar clientes)
1. Haz una copia de seguridad de `taller_parra` en phpMyAdmin (Exportar).
2. En phpMyAdmin selecciona `taller_parra` y usa **Importar** para ejecutar **solo `migracion_v2.sql`**. NO vuelvas a importar `database.sql`.
3. Copia los archivos nuevos sobre `C:\xampp\htdocs\taller_parra\`, conservando la configuración de `config/database.php` si la modificaste.
4. Abre `http://localhost/taller_parra/` y usa los enlaces **Vehículos** y **Órdenes de trabajo**.

## Instalación desde cero
1. Copia la carpeta `taller_parra` a `C:\xampp\htdocs\`.
2. Importa `database.sql` en phpMyAdmin.
3. Ajusta `config/database.php` según tu MySQL.
4. Abre `http://localhost/taller_parra/`.

## Alcance
Clientes, vehículos y órdenes con tipo de servicio, diagnóstico, reparación, costos y estados. La edición de detalles de órdenes y los pagos completos se implementarán después. **No publicar en Internet todavía**: falta autenticación y endurecimiento de seguridad.
