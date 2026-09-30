-- ============================================================
-- DEVIOZ PROYECTOS — Script SQL Completo
-- Versión: 1.0 | Motor: InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS devioz_proyectos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE devioz_proyectos;

-- ============================================================
-- TABLA: usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    correo      VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    estado      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: servicios
-- ============================================================
CREATE TABLE IF NOT EXISTS servicios (
    id_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(120) NOT NULL,
    descripcion TEXT NOT NULL,
    icono       VARCHAR(80) NOT NULL DEFAULT 'bi-gear',
    estado      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: clientes
-- ============================================================
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(120) NOT NULL,
    descripcion     TEXT,
    sector          VARCHAR(80),
    logo            VARCHAR(255),
    web             VARCHAR(255),
    estado          TINYINT(1) NOT NULL DEFAULT 1,
    destacado       TINYINT(1) NOT NULL DEFAULT 0,
    fecha_registro  DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: proyectos
-- ============================================================
CREATE TABLE IF NOT EXISTS proyectos (
    id_proyecto  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente   INT UNSIGNED NOT NULL,
    nombre       VARCHAR(150) NOT NULL,
    descripcion  TEXT,
    tecnologias  VARCHAR(255),
    fecha_inicio DATE,
    fecha_fin    DATE,
    estado       ENUM('Planificado','En desarrollo','Finalizado','Suspendido') NOT NULL DEFAULT 'Planificado',
    imagen       VARCHAR(255),
    destacado    TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_proyecto_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: logros
-- ============================================================
CREATE TABLE IF NOT EXISTS logros (
    id_logro            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_proyecto         INT UNSIGNED NOT NULL,
    titulo              VARCHAR(150) NOT NULL,
    descripcion         TEXT,
    indicador_anterior  DECIMAL(10,2),
    indicador_actual    DECIMAL(10,2),
    porcentaje_mejora   DECIMAL(5,2),
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logro_proyecto FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: contactos
-- ============================================================
CREATE TABLE IF NOT EXISTS contactos (
    id_contacto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    empresa     VARCHAR(120),
    correo      VARCHAR(150) NOT NULL,
    telefono    VARCHAR(30),
    servicio    VARCHAR(120),
    mensaje     TEXT NOT NULL,
    estado      ENUM('Nuevo','Revisado','Contactado','Cerrado') NOT NULL DEFAULT 'Nuevo',
    fecha       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA — USUARIOS
-- Password: Admin2024! (hasheado con PASSWORD_BCRYPT)
-- ============================================================
INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES
('Administrador Devioz', 'admin@devioz.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('Editor Devioz', 'editor@devioz.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor', 1);
-- NOTA: La password hasheada corresponde a la cadena "password" con PASSWORD_DEFAULT.
-- Para producción, generar hash real con password_hash('Admin2024!', PASSWORD_DEFAULT)

-- ============================================================
-- SEED DATA — SERVICIOS
-- ============================================================
INSERT INTO servicios (nombre, descripcion, icono, estado) VALUES
('Desarrollo de Software a Medida', 'Diseñamos y desarrollamos aplicaciones web, móviles y de escritorio adaptadas específicamente a los procesos y necesidades de tu empresa, garantizando escalabilidad y mantenibilidad.', 'bi-code-slash', 1),
('Transformación Digital', 'Acompañamos a las organizaciones en su proceso de transformación digital, desde el diagnóstico hasta la implementación de tecnologías que optimizan operaciones y mejoran la experiencia del cliente.', 'bi-arrow-repeat', 1),
('Business Intelligence & Analytics', 'Convertimos tus datos en inteligencia accionable. Desarrollamos dashboards, reportes y modelos predictivos que apoyan la toma de decisiones estratégicas.', 'bi-bar-chart-line', 1),
('Consultoría de Procesos TI', 'Analizamos, diseñamos y optimizamos los procesos tecnológicos de tu empresa para mejorar la eficiencia operativa y reducir costos.', 'bi-diagram-3', 1),
('Integración de Sistemas', 'Conectamos plataformas, ERPs, CRMs y APIs de terceros para crear un ecosistema tecnológico unificado y eficiente.', 'bi-plug', 1),
('Ciberseguridad y Compliance', 'Protegemos los activos digitales de tu empresa con auditorías de seguridad, gestión de vulnerabilidades y planes de continuidad de negocio.', 'bi-shield-lock', 1);

-- ============================================================
-- SEED DATA — CLIENTES
-- ============================================================
INSERT INTO clientes (nombre, descripcion, sector, logo, web, estado, destacado, fecha_registro) VALUES
('BCP — Banco de Crédito del Perú', 'El Banco de Crédito del Perú es el banco más grande y el proveedor líder de servicios financieros integrados en el Perú. Con Devioz implementamos soluciones de automatización de procesos y analytics avanzado para su área de banca digital.', 'Banca y Finanzas', NULL, 'https://www.viabcp.com', 1, 1, '2022-03-15'),
('Claro Perú', 'Empresa líder en telecomunicaciones en el Perú, parte del grupo América Móvil. Desarrollamos una plataforma de gestión de incidencias técnicas y reportería automatizada para optimizar la atención al cliente empresarial.', 'Telecomunicaciones', NULL, 'https://www.claro.com.pe', 1, 1, '2022-07-20'),
('Molitalia S.A.', 'Empresa peruana líder en la producción y comercialización de alimentos. Implementamos su sistema de trazabilidad de producción y un módulo de planificación de demanda integrado con su ERP.', 'Alimentos y Bebidas', NULL, 'https://www.molitalia.com.pe', 1, 1, '2022-11-10'),
('Costeño Alimentos', 'Empresa peruana dedicada a la producción de arroz, azúcar y fideos. Desarrollamos una aplicación móvil de fuerza de ventas y un sistema de gestión de distribución para optimizar su cadena logística.', 'Alimentos y Bebidas', NULL, 'https://www.costeno.com.pe', 1, 0, '2023-01-25'),
('PUMA Perú', 'La marca deportiva global PUMA en su operación peruana. Desarrollamos su plataforma de e-commerce B2B para distribuidores y un sistema de gestión de inventario multialmacén.', 'Retail y Deportes', NULL, 'https://www.puma.com/pe', 1, 1, '2023-04-12'),
("Chili's Perú", 'Cadena de restaurantes americana con operación en el Perú. Implementamos un sistema de gestión de pedidos en línea integrado con sus cocinas y un módulo de fidelización de clientes.', 'Gastronomía y Restaurantes', NULL, 'https://www.chilis.com.pe', 1, 0, '2023-06-08'),
('Natura Cosméticos Perú', 'Empresa brasileña de cosméticos y cuidado personal con fuerte presencia en Latinoamérica. Desarrollamos su portal de consultoras digitales y un sistema de seguimiento de pedidos en tiempo real.', 'Cosméticos y Bienestar', NULL, 'https://www.natura.com.pe', 1, 1, '2023-08-30'),
('AJE Group', 'Grupo empresarial peruano multinacional líder en bebidas, conocido por marcas icónicas como Sporade (bebida isotónica), Volt (energizante), Pulp (néctares de frutas), Cifrut (jugos cítricos) y Big Cola. Con Devioz desarrollamos una plataforma centralizada de gestión comercial y analytics de ventas por marca y región.', 'Bebidas y Consumo Masivo', NULL, 'https://www.ajegroup.com', 1, 1, '2023-10-05');

-- ============================================================
-- SEED DATA — PROYECTOS
-- ============================================================
INSERT INTO proyectos (id_cliente, nombre, descripcion, tecnologias, fecha_inicio, fecha_fin, estado, imagen, destacado) VALUES
-- BCP (id_cliente = 1)
(1, 'Plataforma de Analytics de Banca Digital', 'Desarrollo de un dashboard ejecutivo con KPIs en tiempo real sobre transacciones digitales, conversión de canales y comportamiento de usuarios. Incluye módulo de alertas automáticas y reportes exportables.', 'Python, React, PostgreSQL, Power BI, AWS', '2022-04-01', '2022-10-31', 'Finalizado', NULL, 1),
(1, 'Automatización de Onboarding Digital', 'Implementación de flujos automáticos para el proceso de apertura de cuentas digitales, reduciendo el tiempo del proceso de 15 minutos a menos de 3 minutos mediante validaciones en línea y firma electrónica.', 'PHP, MySQL, API Reniec, DocuSign', '2023-01-15', '2023-06-30', 'Finalizado', NULL, 0),

-- Claro (id_cliente = 2)
(2, 'Sistema de Gestión de Incidencias TI', 'Plataforma web para el registro, seguimiento y resolución de incidencias técnicas del área de TI empresarial. Con tablero Kanban, SLAs configurables, notificaciones automáticas y reportería avanzada.', 'PHP, MySQL, Bootstrap, JavaScript, Chart.js', '2022-08-01', '2023-01-15', 'Finalizado', NULL, 1),
(2, 'Portal de Autogestión de Servicios', 'Portal self-service para clientes empresariales de Claro que permite consultar facturas, gestionar líneas, solicitar soporte y descargar reportes de consumo sin intervención de agentes.', 'Laravel, Vue.js, MySQL, REST API', '2023-09-01', NULL, 'En desarrollo', NULL, 0),

-- Molitalia (id_cliente = 3)
(3, 'Sistema de Trazabilidad de Producción', 'Implementación de un sistema end-to-end de trazabilidad que permite seguir cada lote de producción desde la materia prima hasta el producto terminado y su distribución, garantizando cumplimiento normativo.', 'PHP, MySQL, QR Codes, Laravel', '2022-12-01', '2023-05-31', 'Finalizado', NULL, 1),
(3, 'Módulo de Planificación de Demanda', 'Desarrollo de un módulo de forecasting de demanda integrado con el ERP de Molitalia (SAP), utilizando modelos estadísticos para optimizar la producción y reducir inventarios.', 'Python, SAP API, MySQL, React', '2023-07-01', NULL, 'En desarrollo', NULL, 0),

-- Costeño (id_cliente = 4)
(4, 'App Móvil de Fuerza de Ventas', 'Aplicación móvil para la fuerza de ventas en campo que permite registrar pedidos, consultar catálogos, verificar stock en tiempo real y gestionar rutas de visita con geolocalización.', 'React Native, PHP, MySQL, Google Maps API', '2023-02-01', '2023-08-31', 'Finalizado', NULL, 0),
(4, 'Sistema de Gestión de Distribución', 'Plataforma para gestionar toda la cadena de distribución: desde el pedido del distribuidor hasta la entrega al punto de venta. Incluye tracking GPS y firma digital de recepción.', 'PHP, MySQL, Bootstrap, Leaflet.js', '2024-01-01', NULL, 'En desarrollo', NULL, 1),

-- PUMA (id_cliente = 5)
(5, 'E-commerce B2B para Distribuidores', 'Plataforma de comercio electrónico exclusiva para distribuidores autorizados de PUMA, con catálogos personalizados por cliente, límites de crédito, aprobación de pedidos y facturación automática.', 'PHP, MySQL, Bootstrap, JavaScript, Stripe API', '2023-05-01', '2023-11-30', 'Finalizado', NULL, 1),
(5, 'Sistema de Inventario Multialmacén', 'Sistema de gestión de inventario que integra 4 almacenes distribuidos en Lima y provincias, con control de stock por SKU, alertas de reposición y sincronización con el e-commerce B2B.', 'PHP, MySQL, Bootstrap, JavaScript', '2024-02-01', NULL, 'En desarrollo', NULL, 0),

-- Chilis (id_cliente = 6)
(6, 'Sistema de Pedidos en Línea', 'Plataforma de pedidos en línea integrada directamente con el sistema de cocina (KDS) de cada local, con gestión de menús digitales, tiempos de preparación en tiempo real y pasarela de pago.', 'PHP, MySQL, Bootstrap, JavaScript, Culqi API', '2023-07-01', '2023-12-31', 'Finalizado', NULL, 0),

-- Natura (id_cliente = 7)
(7, 'Portal Digital de Consultoras', 'Plataforma web y móvil para las consultoras de belleza de Natura, donde pueden gestionar su catálogo personalizado, realizar pedidos, consultar comisiones y acceder a material de capacitación.', 'PHP, MySQL, Bootstrap, PWA', '2023-09-15', '2024-03-31', 'Finalizado', NULL, 1),
(7, 'Sistema de Seguimiento de Pedidos', 'Integración con los sistemas logísticos de Natura para ofrecer tracking en tiempo real de pedidos a consultoras y clientes finales, con notificaciones automáticas por WhatsApp y correo.', 'PHP, MySQL, Twilio API, SendGrid', '2024-04-01', NULL, 'En desarrollo', NULL, 0),

-- AJE Group (id_cliente = 8)
(8, 'Plataforma de Gestión Comercial Multi-Marca', 'Sistema centralizado para gestionar las operaciones comerciales de todas las marcas del grupo AJE: Sporade, Volt, Pulp, Cifrut y Big Cola. Incluye gestión de distribuidores, metas de ventas, visitas de campo y reportería por marca y región.', 'PHP, MySQL, Bootstrap, Chart.js, JavaScript', '2023-11-01', '2024-06-30', 'Finalizado', NULL, 1),
(8, 'Analytics de Ventas por Marca y Región', 'Dashboard de business intelligence que consolida datos de ventas de todas las marcas AJE (Sporade, Volt, Pulp, Cifrut, Big Cola) por región geográfica, canal de venta y segmento, con proyecciones y alertas de desempeño.', 'Python, Power BI, MySQL, ETL pipeline', '2024-07-01', NULL, 'En desarrollo', NULL, 1);

-- ============================================================
-- SEED DATA — LOGROS
-- ============================================================
INSERT INTO logros (id_proyecto, titulo, descripcion, indicador_anterior, indicador_actual, porcentaje_mejora) VALUES
-- Proyecto 1: Analytics BCP
(1, 'Reducción del tiempo de generación de reportes', 'Los reportes ejecutivos que antes tardaban horas en generarse manualmente ahora se producen automáticamente en minutos.', 240.00, 8.00, 96.67),
(1, 'Adopción de canales digitales', 'Incremento en el porcentaje de transacciones realizadas por canales digitales gracias a los insights accionables del dashboard.', 62.00, 84.00, 35.48),

-- Proyecto 2: Onboarding BCP
(2, 'Tiempo de apertura de cuenta digital', 'Reducción drástica del tiempo necesario para completar el proceso de apertura de cuenta en canales digitales.', 15.00, 2.80, 81.33),
(2, 'Tasa de abandono del proceso', 'Reducción de la tasa de abandono durante el proceso de onboarding digital.', 47.00, 12.00, 74.47),

-- Proyecto 3: Incidencias Claro
(3, 'Tiempo promedio de resolución de incidencias (horas)', 'Reducción del tiempo promedio de resolución gracias a la automatización y priorización inteligente.', 18.50, 6.20, 66.49),
(3, 'Satisfacción del cliente interno (NPS)', 'Incremento en el Net Promoter Score del área de soporte TI.', 32.00, 71.00, 121.88),

-- Proyecto 5: Trazabilidad Molitalia
(5, 'Tiempo de rastreo de lote (horas)', 'Reducción del tiempo necesario para rastrear un lote de producto desde producción hasta el punto de venta.', 72.00, 4.00, 94.44),
(5, 'Reducción de mermas', 'Disminución del porcentaje de merma en el proceso productivo gracias a la trazabilidad.', 3.80, 1.20, 68.42),

-- Proyecto 7: App Costeño
(7, 'Pedidos por vendedor al día', 'Incremento en la capacidad de gestión de pedidos por vendedor gracias a la app móvil.', 18.00, 34.00, 88.89),
(7, 'Tiempo de procesamiento de pedido (minutos)', 'Reducción del tiempo de registro y confirmación de pedidos en campo.', 25.00, 6.00, 76.00),

-- Proyecto 9: E-commerce PUMA
(9, 'Tiempo de ciclo de pedido B2B (días)', 'Reducción del tiempo desde el pedido del distribuidor hasta la confirmación y despacho.', 3.50, 0.50, 85.71),
(9, 'Pedidos gestionados mensualmente', 'Incremento en el volumen de pedidos B2B procesados mensualmente sin aumentar personal.', 850.00, 3200.00, 276.47),

-- Proyecto 11: Pedidos Chili's
(11, 'Ventas online (% del total)', 'Incremento en la participación de los canales online en las ventas totales del restaurante.', 8.00, 31.00, 287.50),
(11, 'Tiempo promedio de atención de pedido (minutos)', 'Reducción del tiempo de atención gracias a la integración directa con el sistema de cocina.', 28.00, 19.00, 32.14),

-- Proyecto 12: Portal Natura
(12, 'Consultoras activas digitalmente', 'Incremento en el porcentaje de consultoras que usan el portal digital para gestionar sus pedidos.', 23.00, 78.00, 239.13),
(12, 'Ticket promedio por consultora (soles)', 'Incremento en el ticket promedio de compra de las consultoras que usan el portal digital.', 320.00, 510.00, 59.38),

-- Proyecto 14: Gestión Comercial AJE
(14, 'Cobertura de visitas de campo registradas digitalmente', 'Porcentaje de visitas de campo registradas en el sistema vs total de visitas planificadas.', 45.00, 94.00, 108.89),
(14, 'Tiempo de cierre de reportes comerciales mensuales (días)', 'Reducción del tiempo necesario para consolidar reportes comerciales de todas las marcas AJE.', 12.00, 2.00, 83.33);

-- ============================================================
-- SEED DATA — CONTACTOS
-- ============================================================
INSERT INTO contactos (nombre, empresa, correo, telefono, servicio, mensaje, estado, fecha) VALUES
('Carlos Mendoza', 'TechStart Peru SAC', 'carlos.mendoza@techstart.pe', '994512033', 'Desarrollo de Software a Medida', 'Hola, estamos buscando desarrollar un sistema de gestión de recursos humanos para nuestra empresa. Tenemos alrededor de 150 colaboradores. ¿Podrían enviarnos una propuesta?', 'Contactado', '2024-10-15 09:23:00'),
('Patricia Quispe', 'Inversiones Andinas SA', 'pquispe@invandinas.com', '985632147', 'Business Intelligence & Analytics', 'Necesitamos implementar un sistema de reportería que consolide información de nuestras 5 sucursales. Contamos con SQL Server. ¿Hacen integraciones?', 'Revisado', '2024-11-02 14:15:00'),
('Roberto Silva', 'Distribuidora Silva e Hijos EIRL', 'rsilva@distsilva.com', '976543210', 'Consultoría de Procesos TI', 'Me interesa conocer sus servicios de consultoría para optimizar nuestros procesos de almacén y distribución.', 'Nuevo', '2024-11-20 11:00:00'),
('Lucía Torres', NULL, 'ltorres@gmail.com', NULL, 'Transformación Digital', 'Soy gerente de operaciones de una empresa de logística. Queremos iniciar nuestra transformación digital pero no sabemos por dónde empezar. ¿Ofrecen diagnóstico inicial?', 'Cerrado', '2024-09-10 16:45:00'),
('Miguel Herrera', 'Clínica San Rafael', 'mherrera@clinicasanrafael.pe', '945871236', 'Integración de Sistemas', 'Necesitamos integrar nuestro sistema de citas con nuestra historia clínica electrónica y el módulo de facturación. ¿Tienen experiencia en el sector salud?', 'Nuevo', '2024-12-01 08:30:00');

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
