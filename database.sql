-- Database Schema for registros_RAVAD

CREATE DATABASE IF NOT EXISTS registros_ravad;
USE registros_ravad;

-- General Registry Table
CREATE TABLE IF NOT EXISTS movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    factura VARCHAR(50) DEFAULT NULL,
    detalle VARCHAR(255) NOT NULL,
    debe DECIMAL(10,2) DEFAULT 0.00,
    haber DECIMAL(10,2) DEFAULT 0.00,
    saldo DECIMAL(10,2) DEFAULT 0.00,
    origen VARCHAR(50) DEFAULT 'general',
    foto_factura VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Personas Table
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pro-Luz Contributions Table
CREATE TABLE IF NOT EXISTS pro_luz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    persona_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    mes_correspondiente TINYINT NOT NULL,
    anio_correspondiente INT NOT NULL,
    procesado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);

-- Users Table
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    ultimo_acceso DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default User (Username: admin / Password: admin)
-- The password is 'admin' hashed with PASSWORD_DEFAULT
INSERT INTO usuarios (username, password, nombre_completo) 
SELECT 'admin', '$2y$10$mC7pW0pY/B6v/F.z982OJuL.Zp0I5M6o0l.pS.1rR.Gj3i8.v.52', 'Administrador'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE username = 'admin');
