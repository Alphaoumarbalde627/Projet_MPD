<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302102649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'No-op migration: duplicate baseline schema creation removed.';
    }

    public function up(Schema $schema): void
    {
        // Intentionally left blank.
        // This migration duplicated baseline schema creation from Version20260224202139.
    }

    public function down(Schema $schema): void
    {
        // Intentionally left blank.
    }
}
