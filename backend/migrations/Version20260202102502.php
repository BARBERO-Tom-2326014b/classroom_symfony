<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260202102502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create document table for PDF uploads';
    }

    public function up(Schema $schema): void
    {
        // MySQL: création idempotente pour éviter les erreurs si relancé
        $this->addSql('CREATE TABLE IF NOT EXISTS document (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            pdf_name VARCHAR(255) DEFAULT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS document');
    }
}
