<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222013352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_request DROP FOREIGN KEY `FK_204D45E69280211`');
        $this->addSql('DROP INDEX IDX_204D45E69280211 ON purchase_request');
        $this->addSql('ALTER TABLE purchase_request CHANGE requestedby_id requester_id INT NOT NULL');
        $this->addSql('ALTER TABLE purchase_request ADD CONSTRAINT FK_204D45E6ED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_204D45E6ED442CF4 ON purchase_request (requester_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_request DROP FOREIGN KEY FK_204D45E6ED442CF4');
        $this->addSql('DROP INDEX IDX_204D45E6ED442CF4 ON purchase_request');
        $this->addSql('ALTER TABLE purchase_request CHANGE requester_id requestedby_id INT NOT NULL');
        $this->addSql('ALTER TABLE purchase_request ADD CONSTRAINT `FK_204D45E69280211` FOREIGN KEY (requestedby_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_204D45E69280211 ON purchase_request (requestedby_id)');
    }
}
