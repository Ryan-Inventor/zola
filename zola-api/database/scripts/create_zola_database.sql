-- INF-02 : création de la base Zola (MySQL 8+)
-- Exécuter en tant qu'utilisateur avec droit CREATE DATABASE

CREATE DATABASE IF NOT EXISTS zola
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
