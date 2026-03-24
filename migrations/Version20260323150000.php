<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260323150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize temoignage.produit_id FK and index names to match Doctrine metadata';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE temoignage DROP FOREIGN KEY `FK_BD24D61EF347EFB`');
        $this->addSql('DROP INDEX idx_bd24d61ef347efb ON temoignage');
        $this->addSql('CREATE INDEX IDX_BDADBC46F347EFB ON temoignage (produit_id)');
        $this->addSql('ALTER TABLE temoignage ADD CONSTRAINT `FK_BDADBC46F347EFB` FOREIGN KEY (produit_id) REFERENCES produit (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE temoignage DROP FOREIGN KEY `FK_BDADBC46F347EFB`');
        $this->addSql('DROP INDEX IDX_BDADBC46F347EFB ON temoignage');
        $this->addSql('CREATE INDEX idx_bd24d61ef347efb ON temoignage (produit_id)');
        $this->addSql('ALTER TABLE temoignage ADD CONSTRAINT `FK_BD24D61EF347EFB` FOREIGN KEY (produit_id) REFERENCES produit (id)');
    }
}
