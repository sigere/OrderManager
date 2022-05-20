<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220514091124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add documentName field in repertory entry';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE repertory_entry ADD document_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}
