<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224003136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase_order (id INT AUTO_INCREMENT NOT NULL, order_number VARCHAR(50) DEFAULT NULL, total_amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, purchase_request_id INT NOT NULL, UNIQUE INDEX UNIQ_21E210B24E4DEF6F (purchase_request_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE purchase_order ADD CONSTRAINT FK_21E210B24E4DEF6F FOREIGN KEY (purchase_request_id) REFERENCES purchase_request (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_order DROP FOREIGN KEY FK_21E210B24E4DEF6F');
        $this->addSql('DROP TABLE purchase_order');
    }
}
