<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221205821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase_request (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(255) NOT NULL, quantity INT NOT NULL, justification LONGTEXT NOT NULL, attachment VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, requester_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_204D45E6ED442CF4 (requester_id), INDEX IDX_204D45E64584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE purchase_request ADD CONSTRAINT FK_204D45E6ED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE purchase_request ADD CONSTRAINT FK_204D45E64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product CHANGE price price NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_request DROP FOREIGN KEY FK_204D45E6ED442CF4');
        $this->addSql('ALTER TABLE purchase_request DROP FOREIGN KEY FK_204D45E64584665A');
        $this->addSql('DROP TABLE purchase_request');
        $this->addSql('ALTER TABLE product CHANGE price price DOUBLE PRECISION NOT NULL');
    }
}
