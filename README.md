# Sistema de Gestión para Taller Electromecánico Parra

## Descripción

Sistema web desarrollado para administrar las actividades de un taller electromecánico dedicado a la reparación de alternadores, marchas y sistemas eléctricos automotrices.

El proyecto surge de la necesidad de organizar la información de los clientes, llevar un registro de los trabajos realizados y facilitar la elaboración de cotizaciones. También permite registrar los cobros y gastos del taller para tener un mejor control de sus operaciones.

El sistema está diseñado para ser sencillo de utilizar y adaptarse a las necesidades de un taller pequeño.

## Objetivo

Desarrollar una aplicación web que permita mejorar la administración de un taller electromecánico mediante la digitalización de sus procesos, reduciendo el uso de registros manuales y facilitando el acceso a la información.

## Funcionalidades

### Gestión de clientes
Permite registrar y consultar la información de los clientes del taller.

### Registro de vehículos
Almacena la marca y el modelo de los vehículos asociados a cada cliente, evitando solicitar información innecesaria.

### Órdenes de trabajo
Permite registrar los servicios realizados, incluyendo el tipo de reparación, diagnóstico y estado del trabajo.

### Cotizador de reparaciones
Calcula un precio estimado considerando el costo de las refacciones, la mano de obra y la dificultad de la reparación.

### Generación de tickets
Permite generar e imprimir tickets de cotización en impresoras térmicas de 58 mm y 80 mm.

### Control de ingresos y gastos
Registra los cobros de las reparaciones y los gastos del taller, permitiendo consultar los movimientos financieros.

### Notificaciones por WhatsApp
Facilita la comunicación con los clientes mediante mensajes preparados con información de sus servicios.

### Registro de garantías
Permite establecer períodos y condiciones de garantía que se incluyen en los tickets entregados al cliente.

## Tecnologías utilizadas

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP
- Git y GitHub

## Estructura del proyecto

El sistema está organizado en diferentes módulos para facilitar su mantenimiento y desarrollo.

| Archivo | Descripción |
|---|---|
| `index.php` | Página principal del sistema |
| `vehiculos.php` | Registro y consulta de vehículos |
| `ordenes.php` | Administración de órdenes de trabajo |
| `cotizador.php` | Elaboración y consulta de cotizaciones |
| `finanzas.php` | Registro de cobros y gastos |
| `ticket.php` | Generación de tickets |
| `config/` | Archivos de configuración y funciones auxiliares |
| `assets/` | Archivos de estilos y recursos del sistema |

## Entorno de desarrollo

El proyecto fue desarrollado utilizando XAMPP como servidor local, PHP para la lógica del sistema y MySQL para el almacenamiento de información.

Durante el desarrollo se realizaron pruebas de registro, consulta y actualización de datos, así como de los módulos de cotización y control financiero.

## Instalación

Para ejecutar el proyecto en un entorno local se requiere tener instalado XAMPP o un servidor compatible con PHP y MySQL.

1. Descargar o clonar el repositorio.
2. Colocar la carpeta del proyecto dentro del directorio `htdocs` de XAMPP.
3. Crear la base de datos MySQL e importar su estructura.
4. Configurar la conexión a la base de datos.
5. Iniciar Apache y MySQL.
6. Acceder al sistema desde el navegador.

La estructura de base de datos para una instalación pública de demostración se encuentra pendiente de preparación. Los archivos de configuración con credenciales y los datos reales del taller no forman parte del repositorio.

## Estado del proyecto

El sistema se encuentra en desarrollo y pruebas locales. Actualmente cuenta con módulos para la administración de clientes, vehículos, órdenes de trabajo, cotizaciones, tickets y movimientos financieros.

Entre las mejoras pendientes se encuentran la implementación de autenticación de usuarios, el fortalecimiento de la seguridad y la preparación del sistema para su publicación en un servidor web.

## Autor

Proyecto desarrollado como parte de la aplicación práctica de conocimientos de Ingeniería en Sistemas, enfocado en el desarrollo web y la administración de bases de datos.
