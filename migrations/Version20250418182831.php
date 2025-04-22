<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418182831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse ADD ville VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse ADD pays VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse ADD code_postal VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse ADD actif BOOLEAN NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse ALTER nom_entreprise TYPE VARCHAR(100)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse RENAME COLUMN localisation_warehouse TO adresse_warehouse
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse ADD description TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse DROP ville
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse DROP pays
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse DROP code_postal
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse DROP actif
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse ALTER nom_entreprise TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE warehouse RENAME COLUMN adresse_warehouse TO localisation_warehouse
        SQL);
    }
}
