<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230304134641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('create index IDX_261ABDFD8D9F6D38 on repertory_entry (order_id)');
        $this->addSql('drop index UNIQ_261ABDFD8D9F6D38 on repertory_entry');
    }

    public function down(Schema $schema): void
    {
    }
}
