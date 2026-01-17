# 🛠 Guía de Instalación

Sigue estos pasos para configurar el sistema **RAVAD Ledger** en tu entorno local. El sistema está optimizado para funcionar con **XAMPP** en Windows.

## 1. Requisitos Previos

- [XAMPP](https://www.apachefriends.org/) instalado (Versión con PHP 8.0 o superior).
- Navegador web moderno (Chrome, Edge, Firefox).

## 2. Preparación de Archivos

1.  Descarga o clona el repositorio del sistema.
2.  Copia la carpeta del proyecto (`registros_RAVAD`) dentro del directorio `htdocs` de tu instalación de XAMPP (usualmente `C:\xampp\htdocs\`).

## 3. Configuración de la Base de Datos

1.  Abre el **XAMPP Control Panel** e inicia los módulos **Apache** y **MySQL**.
2.  Accede a [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
3.  Crea una nueva base de datos llamada `registros_ravad`.
4.  Selecciona la base de datos, ve a la pestaña **Importar** y selecciona el archivo `database.sql` que se encuentra en la raíz del proyecto.
5.  Haz clic en **Continuar** para crear las tablas automáticamente.

## 4. Configuración de Conexión

Si has cambiado la contraseña predeterminada de MySQL o usas otros puertos:

1.  Abre el archivo `config/db.php`.
2.  Asegúrate de que los valores coincidan con tu configuración local:
    ```php
    $host = 'localhost';
    $db   = 'registros_ravad';
    $user = 'root';
    $pass = ''; // Contraseña por defecto en XAMPP
    ```

## 5. Acceso al Sistema

Una vez configurado todo, abre tu navegador y visita:
[http://localhost/registros_RAVAD/](http://localhost/registros_RAVAD/)

## ⚠️ Notas Importantes

- **Permisos de Escritura**: Asegúrate de que la carpeta `public/uploads/facturas/` tenga permisos de escritura para poder subir fotos de facturas.
- **Modo Offline**: No necesitas internet para que el sistema funcione, ya que todas las librerías están integradas localmente.
