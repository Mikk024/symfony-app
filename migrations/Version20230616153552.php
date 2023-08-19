<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616153552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE listing ADD COLUMN premium BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__listing AS SELECT id, user_id, name, description, location, file_path FROM listing');
        $this->addSql('DROP TABLE listing');
        $this->addSql('CREATE TABLE listing (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, location VARCHAR(50) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_CB0048D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO listing (id, user_id, name, description, location, file_path) SELECT id, user_id, name, description, location, file_path FROM __temp__listing');
        $this->addSql('DROP TABLE __temp__listing');
        $this->addSql('CREATE INDEX IDX_CB0048D4A76ED395 ON listing (user_id)');
    }
}
