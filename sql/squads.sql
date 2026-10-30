-- Adds the squad tables without deleting the catalog or accounts.
-- Import this file in phpMyAdmin if the site was created before the squad builder.

USE frame_codex;

CREATE TABLE IF NOT EXISTS squads (
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

CREATE TABLE IF NOT EXISTS squad_members (
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
