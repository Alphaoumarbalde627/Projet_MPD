<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260323143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link temoignage to produit and add foreign key';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE temoignage ADD produit_id INT NOT NULL');
        $this->addSql('ALTER TABLE temoignage ADD CONSTRAINT FK_BD24D61EF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_BD24D61EF347EFB ON temoignage (produit_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE temoignage DROP FOREIGN KEY FK_BD24D61EF347EFB');
        $this->addSql('DROP INDEX IDX_BD24D61EF347EFB ON temoignage');
        $this->addSql('ALTER TABLE temoignage DROP produit_id');
    }
}
