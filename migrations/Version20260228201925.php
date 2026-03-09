<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228201925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande CHANGE montant_total montant_total NUMERIC(15, 2) NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande CHANGE prix prix NUMERIC(15, 2) NOT NULL');
        $this->addSql('ALTER TABLE panier CHANGE prix_unitaire prix_unitaire NUMERIC(15, 2) NOT NULL');
        $this->addSql('ALTER TABLE produit CHANGE prix prix NUMERIC(15, 2) NOT NULL');
        $this->addSql('ALTER TABLE promotion CHANGE pourcentage_reduc pourcentage_reduc INT NOT NULL');
        $this->addSql('ALTER TABLE service CHANGE prix prix NUMERIC(15, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande CHANGE montant_total montant_total NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande CHANGE prix prix NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE panier CHANGE prix_unitaire prix_unitaire NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE produit CHANGE prix prix NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE promotion CHANGE pourcentage_reduc pourcentage_reduc NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE service CHANGE prix prix NUMERIC(10, 0) NOT NULL');
    }
}
