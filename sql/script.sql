DROP DATABASE IF EXISTS banque;
CREATE DATABASE banque CHARACTER SET utf8mb4;

USE banque;

-- Admin
CREATE TABLE Admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    mdp VARCHAR(255) NOT NULL
);

-- EF
CREATE TABLE EtablissementFinancier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fond DECIMAL(15, 2) NOT NULL
);

CREATE TABLE historique_EtablissementFinancier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fond DECIMAL(15, 2) NOT NULL,
    type ENUM('ENTREE', 'SORTIE') NOT NULL,
    description VARCHAR(255) NOT NULL
);

-- type de prêt
CREATE TABLE TypePret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    taux DECIMAL(5, 2) NOT NULL -- Taux en pourcentage
);

CREATE TABLE Assurance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    pourcentage FLOAT
);

-- clients
CREATE TABLE Client (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    mdp VARCHAR(255) NOT NULL,
    id_assurance INT DEFAULT NULL,
    FOREIGN KEY (id_assurance) REFERENCES Assurance(id)
);

CREATE TABLE Fond_Client(
    id INT AUTO_INCREMENT PRIMARY KEY,
    compte FLOAT,
    id_client INT DEFAULT NULL,
    FOREIGN KEY (id_client) REFERENCES Client(id)
);

-- gestion de prêts
CREATE TABLE Pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT,
    id_type_pret INT,
    montant DECIMAL(15, 2) NOT NULL,
    date_demande DATE NOT NULL,
    statut ENUM('EN ATTENTE', 'ACCORDE', 'REFUSE', 'ANNULE') DEFAULT 'EN ATTENTE',
    assurance DECIMAL(5, 3) DEFAULT 0.00,
    delai_premier_remboursement INT DEFAULT 0,
    est_valide BOOLEAN DEFAULT FALSE,
    duree_mois INT NOT NULL,
    FOREIGN KEY (id_client) REFERENCES Client(id),
    FOREIGN KEY (id_type_pret) REFERENCES TypePret(id)
);

CREATE TABLE Remboursement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_Pret INT NOT NULL,
    date_debut DATE NOT NULL,
    montant_par_moi DECIMAL NOT NULL,
    nombre_mois INT NOT NULL,
    FOREIGN KEY (id_Pret) REFERENCES Pret(id)
);


-- Insertion de données initiales

INSERT INTO EtablissementFinancier (fond) VALUES (0.00);
INSERT INTO TypePret (nom, taux) VALUES ('Pret personnel', 5.5);
INSERT INTO TypePret (nom, taux) VALUES ('Pret immobilier', 3.2);
INSERT INTO TypePret (nom, taux) VALUES ('Pret automobile', 4.0);

INSERT INTO Client (nom,mdp) VALUES ('Mama Lisa','mamalisa123');

INSERT INTO Admin (nom, mdp) VALUES ('admin', 'admin123');