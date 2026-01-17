# 💻 Documentación Técnica

Esta sección detalla la arquitectura, base de datos y estructura interna del sistema **RAVAD Ledger**.

## 1. Arquitectura del Sistema

El sistema sigue una arquitectura modular sencilla basada en PHP nativo:

- **Root**: Contiene los puntos de entrada principales (`index.php`, `README.md`).
- **config/**: Configuración de la base de datos (`db.php`).
- **includes/**: Componentes reutilizables de UI (`header.php`, `footer.php`) y lógica global (`functions.php`).
- **modules/**: Lógica de negocio dividida en:
  - `dashboard/`: Visualizaciones y KPIs.
  - `pro_luz/`: Gestión de aportaciones y personas.
  - `reportes/`: Generación de reportes y exportación.
  - `general/`: Utilidades como la subida de facturas.
- **public/**: Activos estáticos (CSS, JS, Imágenes y Librerías Vendor).

## 2. Base de Datos (MySQL)

### Tabla: `movimientos`

Almacena todos los registros financieros generales.

- `id`: Int PK.
- `fecha`: Date.
- `factura`: String (opcional).
- `detalle`: Text.
- `debe`: Decimal (Ingresos).
- `haber`: Decimal (Egresos).
- `saldo`: Decimal (Calculado).
- `foto_factura`: String (Nombre del archivo de imagen).
- `es_pro_luz`: Boolean (Marca si viene del módulo Pro-Luz).

### Tabla: `personas`

Gestión de contribuyentes del módulo Pro-Luz.

- `id`: Int PK.
- `nombre`: String.
- `activo`: Boolean (Baja lógica).
- `total_historico`: Decimal (Suma acumulada de todas sus contribuciones).

### Tabla: `pro_luz`

Registros individuales de aportaciones mensuales.

- `id`: Int PK.
- `persona_id`: FK -> personas.
- `monto`: Decimal.
- `mes_correspondiente` / `anio_correspondiente`: Int.

### Tabla: `usuarios`

Control de acceso al sistema.

- `id`: Int PK.
- `username`: String (Único).
- `password`: String (Hash seguro).
- `nombre_completo`: String.
- `ultimo_acceso`: Datetime.

## 3. Seguridad y Sesiones

El sistema implementa una capa de seguridad basada en sesiones nativas de PHP:

- **Middleware**: El archivo `includes/auth_check.php` se incluye en todas las cabeceras para validar que el usuario tenga una sesión activa.
- **Protección de Handlers**: Los scripts que procesan datos (subida de archivos, guardado) tienen validaciones internas para evitar acceso directo malintencionado.
- **Contraseñas**: Se utiliza la función `password_hash()` con el algoritmo por defecto de PHP para el almacenamiento seguro.

## 4. Gestión de Activos (Modo Offline)

Todas las librerías externas se encuentran en `public/vendor/`. No se deben añadir scripts de CDNs externos para mantener la compatibilidad offline.

- **Bootstrap 5**: Estructura y componentes.
- **jQuery 3.7.1**: Dependencia base para componentes interactivos.
- **DataTables.js**: Motor de tablas con búsqueda, paginación y ordenamiento.
- **Chart.js**: Renderizado de Canvas para el Dashboard.
- **SweetAlert2**: Gestor de diálogos y notificaciones.
- **SheetJS / jsPDF**: Procesamiento de documentos en el lado del cliente.

## 5. Mejores Prácticas implementadas

- **Baja Lógica**: En lugar de eliminar personas, se utiliza la columna `activo` para preservar el historial de reportes.
- **Sanitización**: Uso de sentencias preparadas (PDO) para toda la interacción con la base de datos.
- **Responsividad**: Diseño móvil-primero utilizando el sistema de rejilla de Bootstrap 5.
