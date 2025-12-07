-- SQL Script pour créer les tables nécessaires
-- Base de données: ashkili

-- Table des clients
CREATE TABLE IF NOT EXISTS clients (
    client_id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des admins
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'Support',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) DEFAULT 1
);

-- Table des colis
CREATE TABLE IF NOT EXISTS colis (
    colis_id INT PRIMARY KEY AUTO_INCREMENT,
    code_suivi VARCHAR(50) NOT NULL UNIQUE,
    client_id INT NOT NULL,
    statut_actuel VARCHAR(100) DEFAULT NULL,
    date_expedition DATE DEFAULT NULL,
    date_livraison_prevue DATE DEFAULT NULL,
    nom_client VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    price INT NOT NULL,
    phone_client VARCHAR(20) NOT NULL,
    phone_comp VARCHAR(20) NULL,
    poids DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE
);

-- Table historique_statuts_colis
CREATE TABLE IF NOT EXISTS historique_statuts_colis (
    historique_id INT PRIMARY KEY AUTO_INCREMENT,
    colis_id INT NOT NULL,
    statut VARCHAR(150) NOT NULL,
    lieu VARCHAR(100) DEFAULT NULL,
    date_heure DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colis_id) REFERENCES colis(colis_id) ON DELETE CASCADE
);

-- Table des reclamations
CREATE TABLE IF NOT EXISTS reclamations (
    reclamation_id INT PRIMARY KEY AUTO_INCREMENT,
    colis_id INT NOT NULL,
    client_id INT NOT NULL,
    type_reclamation VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    statut_reclamation VARCHAR(50) DEFAULT 'جديد',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    response_admin TEXT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    FOREIGN KEY (colis_id) REFERENCES colis(colis_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE SET NULL
);
CREATE TABLE reponses_reclamations (
	reponse_id INT NOT NULL AUTO_INCREMENT,
	reclamation_id INT NOT NULL,
	admin_id INT NOT NULL,
	contenu_reponse TEXT NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	date_reponse DATETIME NOT NULL,
	lu_par_client TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (reponse_id) USING BTREE,
	INDEX reclamation_id (reclamation_id) USING BTREE,
	INDEX admin_id (admin_id) USING BTREE,
	CONSTRAINT reponses_reclamations_ibfk_1 FOREIGN KEY (reclamation_id) REFERENCES reclamations (reclamation_id) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT reponses_reclamations_ibfk_2 FOREIGN KEY (admin_id) REFERENCES admins (admin_id) ON UPDATE NO ACTION ON DELETE NO ACTION
)
-- Indexes pour améliorer les performances
CREATE INDEX idx_client_email ON clients(email);
CREATE INDEX idx_admin_email ON admins(email);
CREATE INDEX idx_colis_code_suivi ON colis(code_suivi);
CREATE INDEX idx_colis_client ON colis(client_id);
CREATE INDEX idx_historique_colis ON historique_statuts_colis(colis_id);
CREATE INDEX idx_reclamation_client ON reclamations(client_id);
CREATE INDEX idx_reclamation_colis ON reclamations(colis_id);

-- Note: If you already created the colis table without the additional columns,
-- you need to run this ALTER TABLE command separately:
-- ALTER TABLE colis
--     ADD COLUMN nom_client VARCHAR(255) NOT NULL,
--     ADD COLUMN location VARCHAR(255) NOT NULL,
--     ADD COLUMN price INT NOT NULL,
--     ADD COLUMN phone_client VARCHAR(20) NOT NULL,
--     ADD COLUMN phone_comp VARCHAR(20) NULL,
--     ADD COLUMN poids DECIMAL(10, 2) NOT NULL;

-- ALTER TABLE reclamations
--     ADD COLUMN lu_par_admin TINYINT(1) DEFAULT 0 NOT NULL,
--     ADD COLUMN lu_par_client TINYINT(1) DEFAULT 0 NOT NULL;    