<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115090308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_order DROP FOREIGN KEY `FK_21E210B24E4DEF6F`');
        $this->addSql('ALTER TABLE purchase_order ADD CONSTRAINT FK_21E210B24E4DEF6F FOREIGN KEY (purchase_request_id) REFERENCES purchase_request (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_order DROP FOREIGN KEY FK_21E210B24E4DEF6F');
        $this->addSql('ALTER TABLE purchase_order ADD CONSTRAINT `FK_21E210B24E4DEF6F` FOREIGN KEY (purchase_request_id) REFERENCES purchase_request (id)');
    }
}
