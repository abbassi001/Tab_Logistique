<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417165952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Étape 1 : Ajouter la colonne, mais nullable pour éviter l'erreur
        // $this->addSql('ALTER TABLE warehouse ADD nom VARCHAR(255) DEFAULT NULL');
    
        // Étape 2 : Mettre une valeur par défaut pour les lignes existantes
        $this->addSql("UPDATE warehouse SET nom = 'Entrepôt temporaire' WHERE nom IS NULL");
    
        // Étape 3 : Rendre la colonne NOT NULL après la mise à jour
        $this->addSql('ALTER TABLE warehouse ALTER COLUMN nom SET NOT NULL');
    }
    

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse DROP nom
        SQL);
    }
}
