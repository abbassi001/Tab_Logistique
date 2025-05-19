<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250501165705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fusion des entités User et Employe avec gestion des contraintes';
    }

    public function up(Schema $schema): void
    {
        // 1. Désactiver temporairement la vérification des contraintes
        $this->addSql("SET session_replication_role = 'replica'");
        
        // 2. Les étapes de suppression de tables et contraintes
        $this->addSql('DROP SEQUENCE IF EXISTS activity_log_id_seq CASCADE');
        $this->addSql('ALTER TABLE activity_log DROP CONSTRAINT IF EXISTS fk_fd06f647a76ed395');
        $this->addSql('DROP TABLE IF EXISTS activity_log');
        
        // 3. Modifications sur la table colis
        $this->addSql('ALTER TABLE colis DROP IF EXISTS updated_at');
        $this->addSql('ALTER TABLE colis DROP IF EXISTS deleted_at');
        $this->addSql('ALTER TABLE colis DROP IF EXISTS deleted_by');
        
        // 4. Modifications sur la table statut - suppression des contraintes
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT IF EXISTS fk_e564f0bf1b65292');
        $this->addSql('DROP INDEX IF EXISTS idx_e564f0bf1b65292');
        
        // 5. Créer une copie des données employe dans une table temporaire pour migration
        $this->addSql('CREATE TABLE IF NOT EXISTS employe_backup AS SELECT * FROM employe');
        
        // 6. Mise à jour de la table user avec les nouveaux champs 
        $this->addSql('ALTER TABLE "user" ADD telephone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD niveau_acces VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD date_contrat DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD poste VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD departement_id INT DEFAULT NULL');
        
        // 7. Copier les données de employe vers user
        $this->addSql('UPDATE "user" u 
                      SET telephone = e.telephone,
                          departement_id = NULL,
                          niveau_acces = e.niveau_acces
                      FROM employe e 
                      WHERE e.email = u.email');
        
        // 8. Mise à jour de la table statut
        $this->addSql('ALTER TABLE statut RENAME COLUMN employe_id TO user_id');
        
        // 9. Mettre à jour les références dans statut
        $this->addSql('UPDATE statut s
                      SET user_id = u.id 
                      FROM "user" u 
                      JOIN employe e ON e.email = u.email
                      WHERE s.user_id = e.id');
                      
        // 10. Supprimer employe_id de user s'il existe (ou le renommer si déjà fait)
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS employe_id');
        
        // 11. Réactiver les contraintes 
        $this->addSql('SET session_replication_role = "origin"');
        
        // 12. Ajouter les contraintes sur user et statut
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649CCF9E01E ON "user" (departement_id)');
        
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BFA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E564F0BFA76ED395 ON statut (user_id)');
    }

    public function down(Schema $schema): void
    {
        // Cette méthode ne sera généralement pas utilisée
        // mais fournie pour la complétude
        $this->addSql('SET session_replication_role = "replica"');
        
        // Supprimer les contraintes ajoutées
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT IF EXISTS FK_E564F0BFA76ED395');
        $this->addSql('DROP INDEX IF EXISTS IDX_E564F0BFA76ED395');
        
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT IF EXISTS FK_8D93D649CCF9E01E');
        $this->addSql('DROP INDEX IF EXISTS IDX_8D93D649CCF9E01E');
        
        // Restaurer les anciennes colonnes/contraintes à partir de la sauvegarde
        // (Remarque: cela suppose que la table employe_backup existe toujours)
        $this->addSql('ALTER TABLE statut RENAME COLUMN user_id TO employe_id');
        
        // Supprimer les colonnes ajoutées à l'utilisateur
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS telephone');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS niveau_acces');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS date_contrat');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS poste');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS description');
        $this->addSql('ALTER TABLE "user" DROP IF EXISTS departement_id');
        $this->addSql('ALTER TABLE "user" ADD employe_id INT DEFAULT NULL');
        
        // Réactiver les contraintes
        $this->addSql('SET session_replication_role = "origin"');
    }
    
    public function postUp(Schema $schema): void
    {
        // S'assurer que les contraintes sont réactivées après l'exécution
        $this->connection->executeStatement("SET session_replication_role = 'origin'");
    }
}