<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260323143426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'No-op placeholder for previously executed duplicate migration';
    }

    public function up(Schema $schema): void
    {
        // No-op: this version was already executed and superseded.
    }

    public function down(Schema $schema): void
    {
        // No-op.
    }
}
