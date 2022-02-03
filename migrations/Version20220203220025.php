<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220203220025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE repertory_entry (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, document_issuer VARCHAR(255) DEFAULT NULL, comments VARCHAR(255) DEFAULT NULL, copies INT NOT NULL, number INT NOT NULL, year INT NOT NULL, document_date DATE DEFAULT NULL, copy_price NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_261ABDFD8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE repertory_entry ADD CONSTRAINT FK_261ABDFD8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE `order` DROP discr, DROP document_issuer, DROP comments, DROP copies, DROP number, DROP document_date, DROP copy_price');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE repertory_entry');
        $this->addSql('ALTER TABLE `order` ADD discr VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ADD document_issuer VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD comments VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD copies INT DEFAULT NULL, ADD number INT DEFAULT NULL, ADD document_date DATE DEFAULT NULL, ADD copy_price NUMERIC(10, 2) DEFAULT NULL');
    }
}
