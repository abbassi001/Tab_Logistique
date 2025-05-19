<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250501170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fusion des entités User et Employe en douceur';
    }

    public function up(Schema $schema): void
    {
        // 1. AJOUT DE NOUVELLES COLONNES À USER
        $this->addSql('ALTER TABLE "user" ADD telephone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD niveau_acces VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD date_contrat DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD poste VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD departement_id INT DEFAULT NULL');
        
        // 2. CRÉER TABLE TEMPORAIRE POUR LA MIGRATION DES DONNÉES
        $this->addSql('CREATE TABLE IF NOT EXISTS user_employe_map (
            user_id INT NOT NULL,
            employe_id INT NOT NULL,
            PRIMARY KEY (user_id, employe_id)
        )');
        
        // 3. REMPLIR LA TABLE DE MAPPING À PARTIR DES RELATIONS EXISTANTES
        $this->addSql('INSERT INTO user_employe_map (user_id, employe_id)
                        SELECT u.id, u.employe_id FROM "user" u 
                        WHERE u.employe_id IS NOT NULL');
        
        // 4. AJOUTER DES MAPPINGS SUPPLÉMENTAIRES PAR EMAIL
        $this->addSql('INSERT INTO user_employe_map (user_id, employe_id)
                        SELECT u.id, e.id FROM "user" u 
                        JOIN employe e ON u.email = e.email
                        WHERE NOT EXISTS (
                            SELECT 1 FROM user_employe_map m 
                            WHERE m.user_id = u.id
                        )');
        
        // 5. MIGRER LES DONNÉES D'EMPLOYE VERS USER
        $this->addSql('UPDATE "user" u
                        SET telephone = e.telephone,
                            niveau_acces = e.niveau_acces
                        FROM employe e
                        JOIN user_employe_map m ON e.id = m.employe_id
                        WHERE u.id = m.user_id');
        
        // 6. AJOUTER LA NOUVELLE COLONNE DANS STATUT AVANT DE SUPPRIMER L'ANCIENNE
        $this->addSql('ALTER TABLE statut ADD user_id INT DEFAULT NULL');
        
        // 7. MIGRER LES RÉFÉRENCES DE STATUT
        $this->addSql('UPDATE statut s
                        SET user_id = m.user_id
                        FROM user_employe_map m
                        WHERE s.employe_id = m.employe_id');
        
        // 8. SUPPRIMER LA COLONNE EMPLOYE_ID DE STATUT (EN DEUX ÉTAPES)
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT IF EXISTS fk_e564f0bf1b65292');
        $this->addSql('DROP INDEX IF EXISTS idx_e564f0bf1b65292');
        $this->addSql('ALTER TABLE statut DROP employe_id');
        
        // 9. SUPPRIMER LA COLONNE EMPLOYE_ID DE USER (EN DEUX ÉTAPES)
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT IF EXISTS fk_8d93d6491b65292');
        $this->addSql('DROP INDEX IF EXISTS uniq_8d93d6491b65292');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS employe_id');
        
        // 10. AJOUTER LES CONTRAINTES POUR LES NOUVELLES RELATIONS
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649CCF9E01E 
                        FOREIGN KEY (departement_id) REFERENCES departement (id) 
                        DEFERRABLE INITIALLY DEFERRED');
        $this->addSql('CREATE INDEX IDX_8D93D649CCF9E01E ON "user" (departement_id)');
        
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BFA76ED395 
                        FOREIGN KEY (user_id) REFERENCES "user" (id) 
                        DEFERRABLE INITIALLY DEFERRED');
        $this->addSql('CREATE INDEX IDX_E564F0BFA76ED395 ON statut (user_id)');
        
        // 11. NETTOYAGE - SUPPRIMER LA TABLE TEMPORAIRE
        $this->addSql('DROP TABLE IF EXISTS user_employe_map');
    }

    public function down(Schema $schema): void
    {
        // Cette méthode peut être simplifiée car il n'est généralement pas pratique
        // de défaire complètement cette migration
        
        // 1. Supprimer les contraintes
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT IF EXISTS FK_E564F0BFA76ED395');
        $this->addSql('DROP INDEX IF EXISTS IDX_E564F0BFA76ED395');
        
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT IF EXISTS FK_8D93D649CCF9E01E');
        $this->addSql('DROP INDEX IF EXISTS IDX_8D93D649CCF9E01E');
        
        // 2. Supprimer les nouvelles colonnes
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS telephone');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS niveau_acces');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS date_contrat');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS poste');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS description');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS departement_id');
        
        // 3. Ajouter employe_id à statut (mais sans les données)
        $this->addSql('ALTER TABLE statut ADD employe_id INT DEFAULT NULL');
    }
}