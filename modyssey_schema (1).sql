CREATE DATABASE IF NOT EXISTS modyssey;
USE modyssey;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS mod_category;
DROP TABLE IF EXISTS mod_image;
DROP TABLE IF EXISTS `mod`;
DROP TABLE IF EXISTS category;
DROP TABLE IF EXISTS game;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS role;
SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE role (
    IDRole INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (IDRole),
    UNIQUE KEY uq_role_name (name)
);
INSERT INTO role (name)
VALUES ('guest'),
    ('user'),
    ('sympathizer'),
    ('admin');
CREATE TABLE user (
    IDUser INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    IDRole INT NOT NULL DEFAULT 2,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (IDUser),
    UNIQUE KEY uq_user_username (username),
    UNIQUE KEY uq_user_email (email),
    CONSTRAINT fk_users_role FOREIGN KEY (IDRole) REFERENCES role (IDRole)
);
CREATE TABLE game (
    IDGame INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    added_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (IDGame),
    UNIQUE KEY uq_game_name (name),
    CONSTRAINT fk_game_user FOREIGN KEY (added_by) REFERENCES user (IDUser)
);
CREATE TABLE category (
    IDCategory INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(100) NOT NULL,
    game_id INT NOT NULL,
    added_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (IDCategory),
    UNIQUE KEY uq_category_name_game (name, game_id),
    CONSTRAINT fk_category_user FOREIGN KEY (added_by) REFERENCES user (IDUser),
    CONSTRAINT fk_category_game FOREIGN KEY (game_id) REFERENCES game (IDGame) ON DELETE CASCADE
);
CREATE TABLE `mod` (
    IDMod INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    cover_image_path VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    visibility ENUM('public', 'private') NOT NULL DEFAULT 'public',
    download_count INT NOT NULL DEFAULT 0,
    game_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (IDMod),
    CONSTRAINT fk_mod_game FOREIGN KEY (game_id) REFERENCES game (IDGame) ON DELETE CASCADE,
    CONSTRAINT fk_mod_user FOREIGN KEY (uploaded_by) REFERENCES user (IDUser)
);
CREATE TABLE mod_category (
    mod_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (mod_id, category_id),
    CONSTRAINT fk_modcat_mod FOREIGN KEY (mod_id) REFERENCES `mod` (IDMod) ON DELETE CASCADE,
    CONSTRAINT fk_modcat_category FOREIGN KEY (category_id) REFERENCES category (IDCategory) ON DELETE CASCADE
);
CREATE TABLE mod_image (
    IDModImage INT NOT NULL AUTO_INCREMENT,
    mod_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (IDModImage),
    CONSTRAINT fk_modimage_mod FOREIGN KEY (mod_id) REFERENCES `mod` (IDMod) ON DELETE CASCADE
);