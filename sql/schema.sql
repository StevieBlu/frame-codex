-- Frame Codex tables.
-- In phpMyAdmin, create a database named frame_codex, select it, then import this file.
-- Importing this file again deletes the tables, including accounts and favorites.

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS frame_codex
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE frame_codex;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS squad_members;
DROP TABLE IF EXISTS squads;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS abilities;
DROP TABLE IF EXISTS warframe_roles;
DROP TABLE IF EXISTS warframes;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(20) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY users_username (username),
  UNIQUE KEY users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(40) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE warframes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  category ENUM('warframe', 'prime', 'protoframe') NOT NULL,
  related_id INT UNSIGNED NULL,
  summary TEXT NOT NULL,
  passive_name VARCHAR(80) NOT NULL,
  passive_summary TEXT NOT NULL,
  health INT UNSIGNED NOT NULL,
  shields INT UNSIGNED NOT NULL,
  armor INT UNSIGNED NOT NULL,
  energy INT UNSIGNED NOT NULL,
  accent CHAR(7) NOT NULL,
  mechanics JSON NULL,
  PRIMARY KEY (id),
  UNIQUE KEY warframes_slug (slug),
  KEY warframes_category_name (category, name),
  CONSTRAINT warframes_related_fk
    FOREIGN KEY (related_id) REFERENCES warframes (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE warframe_roles (
  warframe_id INT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (warframe_id, role_id),
  CONSTRAINT warframe_roles_frame_fk
    FOREIGN KEY (warframe_id) REFERENCES warframes (id)
    ON DELETE CASCADE,
  CONSTRAINT warframe_roles_role_fk
    FOREIGN KEY (role_id) REFERENCES roles (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE abilities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  warframe_id INT UNSIGNED NOT NULL,
  slot TINYINT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  summary TEXT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY abilities_frame_slot (warframe_id, slot),
  CONSTRAINT abilities_frame_fk
    FOREIGN KEY (warframe_id) REFERENCES warframes (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE squads (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(40) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY squads_user (user_id, created_at),
  CONSTRAINT squads_user_fk
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE squad_members (
  squad_id INT UNSIGNED NOT NULL,
  slot TINYINT UNSIGNED NOT NULL,
  warframe_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (squad_id, slot),
  CONSTRAINT squad_members_squad_fk
    FOREIGN KEY (squad_id) REFERENCES squads (id)
    ON DELETE CASCADE,
  CONSTRAINT squad_members_frame_fk
    FOREIGN KEY (warframe_id) REFERENCES warframes (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE favorites (
  user_id INT UNSIGNED NOT NULL,
  warframe_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, warframe_id),
  CONSTRAINT favorites_user_fk
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE,
  CONSTRAINT favorites_frame_fk
    FOREIGN KEY (warframe_id) REFERENCES warframes (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
