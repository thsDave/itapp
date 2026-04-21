-- ============================================================
-- Migration 001 — Seed data
-- All passwords are: password123  (bcrypt cost 12)
-- ============================================================

USE itapp;

-- ── status ───────────────────────────────────────────────────
-- idstatus: 1=active  2=inactive  3=deleted
INSERT INTO `status` (idstatus, status) VALUES
(1, 'active'),
(2, 'inactive'),
(3, 'deleted');


-- ── users ────────────────────────────────────────────────────
INSERT INTO users (name, email, password, role, idstatus) VALUES
(
    'Alice Martínez',
    'alice@example.com',
    '$2y$12$oPnYHDe/Ixb7DG6BUnIohefRUGi0bZgPw15.i3jhnidij97cTVWbq',
    'admin',
    1
),
(
    'Bruno Ramírez',
    'bruno@example.com',
    '$2y$12$/Oa5eG8viqz.vTyBlL/6AOicq4dyWuKmZVqUIYyLARkG16vNph.le',
    'consultant',
    1
),
(
    'Carmen López',
    'carmen@example.com',
    '$2y$12$Q3xsPHvV7Sds2qd6xrYgM.EUWhEll4ngcfEdeg3/bK/cfZvWCQqQm',
    'user',
    1
),
(
    'Diego Flores',
    'diego@example.com',
    '$2y$12$6nUxNd/LVimTAzzCH5T4..BZ0E71/VBdmEJYMDmJhH3zuoGxcxjRa',
    'consultant',
    1
),
(
    'Elena Torres',
    'elena@example.com',
    '$2y$12$N3ApCbjsy61D9I9wH64s.ek4Dkox/Kp8Hu7MruAcGYasJRgXqzXDS',
    'user',
    2
);


-- ── areas ────────────────────────────────────────────────────
INSERT INTO areas (name) VALUES
('Tecnología'),
('Recursos Humanos'),
('Contabilidad'),
('Administración'),
('Dirección General'),
('Marketing'),
('Operaciones');


-- ── collaborators ────────────────────────────────────────────
-- area_id: 1=Tecnología, 2=RR.HH., 3=Contabilidad,
--          4=Administración, 5=Dirección, 7=Operaciones
-- idstatus: 1=active
INSERT INTO collaborators (name, position, country, area_id, idstatus, entry_date, exit_date, assigned_equipment) VALUES
(
    'Roberto Salinas',
    'Contador',
    'México',
    3,
    1,
    '2021-03-15',
    NULL,
    'Laptop Dell Latitude 5520, Monitor LG 24"'
),
(
    'Patricia Vega',
    'Recursos Humanos',
    'México',
    2,
    1,
    '2020-07-01',
    NULL,
    'Laptop HP EliteBook 840, Impresora HP LaserJet'
),
(
    'Miguel Ángel Cruz',
    'Desarrollador de Software',
    'México',
    1,
    1,
    '2022-01-10',
    NULL,
    'MacBook Pro 14", Monitor Dell 27", Teclado mecánico'
),
(
    'Sandra Morales',
    'Diseñadora Gráfica',
    'Colombia',
    1,
    1,
    '2019-11-20',
    '2024-06-30',
    'iMac 24", Tableta Wacom Intuos Pro'
),
(
    'Héctor Jiménez',
    'Soporte Técnico',
    'México',
    1,
    1,
    '2023-04-05',
    NULL,
    'Laptop Lenovo ThinkPad E15, Kit de herramientas'
),
(
    'Laura Gutiérrez',
    'Gerente de Proyectos',
    'Argentina',
    4,
    1,
    '2018-09-12',
    NULL,
    'Laptop HP ProBook 455, Monitor Samsung 32", Teléfono IP'
),
(
    'Carlos Mendoza',
    'Analista de Sistemas',
    'México',
    1,
    1,
    '2022-08-22',
    NULL,
    'Laptop Asus ExpertBook B9, Docking Station'
);


-- ── supports ─────────────────────────────────────────────────
INSERT INTO supports (collaborator_id, user_id, title, description, attention_level, status, notes) VALUES
(
    1, 2,
    'Equipo no enciende',
    'La laptop Dell Latitude 5520 de Roberto no enciende desde esta mañana. Ya intentó conectar el cargador pero sin resultado.',
    'high',
    'in_progress',
    'Se revisó la batería. Posible fallo en la tarjeta madre. Se solicitó repuesto.'
),
(
    2, 4,
    'Problemas con impresora HP',
    'La impresora no reconoce los cartuchos nuevos que se instalaron ayer. El sistema reporta cartuchos incompatibles.',
    'medium',
    'open',
    NULL
),
(
    3, 2,
    'Acceso denegado a repositorio Git',
    'Miguel Ángel no puede hacer push al repositorio principal desde hace dos días. Error 403 al intentar conectarse.',
    'high',
    'closed',
    'Se regeneraron las credenciales SSH y se actualizaron los permisos en el servidor. Resuelto el 2024-10-14.'
),
(
    5, NULL,
    'Teclado con teclas pegadas',
    'Héctor reporta que varias teclas de su ThinkPad E15 no responden correctamente después de derramar líquido.',
    'medium',
    'open',
    NULL
),
(
    6, 4,
    'Lentitud extrema en equipo',
    'La laptop de Laura tarda más de 10 minutos en iniciar y el sistema se congela constantemente durante el trabajo.',
    'critical',
    'in_progress',
    'Se realizó limpieza de disco. Pendiente ampliar RAM de 8GB a 16GB.'
),
(
    7, 2,
    'Instalación de software de análisis',
    'Carlos necesita instalar Power BI Desktop y Python 3.12 con las librerías pandas, numpy y matplotlib.',
    'low',
    'closed',
    'Instalación completada. Se creó entorno virtual Python y se verificó integración con Power BI.'
),
(
    1, NULL,
    'Correo no sincroniza en Outlook',
    'Roberto reporta que sus correos de los últimos 3 días no aparecen en Outlook aunque sí están en el webmail.',
    'medium',
    'open',
    NULL
),
(
    3, 4,
    'Solicitud de segundo monitor',
    'Miguel Ángel solicita la instalación y configuración de un segundo monitor externo para su estación de trabajo.',
    'low',
    'closed',
    'Monitor Dell P2422H instalado y configurado en modo extendido.'
),
(
    4, 2,
    'Recuperación de archivos de diseño',
    'Sandra reportó antes de su salida que varios archivos PSD en el iMac quedaron corruptos tras un apagón.',
    'critical',
    'closed',
    'Se recuperaron 14 de 17 archivos desde el backup de Time Machine del día anterior.'
),
(
    6, NULL,
    'Configuración VPN para trabajo remoto',
    'Laura necesita acceso VPN configurado para conectarse a los servidores internos desde casa.',
    'medium',
    'open',
    NULL
);
