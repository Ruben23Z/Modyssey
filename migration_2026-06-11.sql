-- Migração 2026-06-11
-- Executar em bases de dados já existentes (o schema.sql já inclui estas colunas para instalações novas)

-- 1. Preferência de idioma do utilizador (usada no site e nos emails de subscrição)
ALTER TABLE user ADD COLUMN lang VARCHAR(5) NOT NULL DEFAULT 'pt';

-- 2. Extensões de ficheiro permitidas para mods, definidas por jogo
--    Lista separada por vírgulas (ex: 'zip,rar,7z,pak'). NULL/vazio = aceita .zip por omissão.
ALTER TABLE game ADD COLUMN allowed_extensions VARCHAR(255) NOT NULL DEFAULT 'zip';
