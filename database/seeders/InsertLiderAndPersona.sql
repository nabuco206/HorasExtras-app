-- Inserta primero en tbl_lider
INSERT INTO tbl_lider (username, created_at, updated_at) VALUES ('crojasm', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- Luego inserta en tbl_personas
INSERT INTO tbl_personas (Nombre, Apellido, UserName, cod_fiscalia, id_escalafon, created_at, updated_at)
VALUES ('cristian', 'rojas', 'crojasm', 2, 1, '2025-05-25 01:02:53', '2025-05-25 01:02:53');
