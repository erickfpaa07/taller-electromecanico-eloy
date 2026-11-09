TALLER PARRA V3 — COTIZADOR
1. Respalda tu base de datos desde phpMyAdmin > Exportar.
2. Selecciona taller_parra e importa migracion_v3.sql una sola vez.
3. Copia el contenido de la carpeta taller_parra del ZIP a C:\xampp\htdocs\taller_parra, reemplazando archivos. Conserva tu config/database.php si lo personalizaste.
4. Abre http://localhost/taller_parra/cotizador.php
5. Los tiempos de ejemplo (alternador 2 h, marcha 2 h, cableado 3 h, diagnostico 1 h) se configuran en cotizador.php y deben validarse con el taller.
6. El precio final es un único monto visible al cliente; se guardan costos internos para control. No incluye impuestos.
7. Para publicar en Internet falta autenticación, permisos y medidas de seguridad.
