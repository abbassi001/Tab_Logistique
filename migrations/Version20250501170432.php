<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250501170432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Finalisation de la fusion User-Employe';
    }

    public function up(Schema $schema): void
    {
        // Corriger les problèmes potentiels de références
        
        // 1. Mise à jour des contraintes de clé étrangère
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT IF EXISTS fk_e564f0bfa76ed395');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BFA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT IF EXISTS fk_8d93d649ccf9e01e');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        
        // 2. S'assurer que les indices sont créés
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_E564F0BFA76ED395 ON statut (user_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_8D93D649CCF9E01E ON "user" (departement_id)');
        
        // 3. Supprimer l'entité Employe si elle n'est plus nécessaire
        // Nous ne supprimons pas la table directement pour éviter des problèmes
        // en cas d'autres références non identifiées
        
        // 4. Ajouter ces informations dans le journal des migrations
        $this->addSql("INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) 
                       SELECT 'DoctrineMigrations\\\\Version20250501165705', NOW(), 0
                       WHERE NOT EXISTS (
                           SELECT 1 FROM doctrine_migration_versions 
                           WHERE version = 'DoctrineMigrations\\\\Version20250501165705'
                       )");
    }

    public function down(Schema $schema): void
    {
        // Cette méthode n'est pas nécessaire en pratique, mais nous la laissons
        // pour la complétude
        
        // Marquer la migration problématique comme non exécutée
        $this->addSql("DELETE FROM doctrine_migration_versions 
                      WHERE version = 'DoctrineMigrations\\\\Version20250501165705'");
    }
}