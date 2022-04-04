<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220404175313 extends AbstractMigration
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
        $this->addSql('ALTER TABLE `order` ADD additional_fee NUMERIC(10, 2) DEFAULT NULL');

        $this->addSql('UPDATE `order` SET additional_fee = 0 WHERE additional_fee is null');

        $this->addSql('UPDATE `order` SET state = "accepted" WHERE state = "przyjete"');
        $this->addSql('UPDATE `order` SET state = "done" WHERE state = "wykonane"');
        $this->addSql('UPDATE `order` SET state = "sent" WHERE state = "wyslane"');
        $this->addSql('UPDATE `user` set preferences = "{}" WHERE id <> 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE repertory_entry');
        $this->addSql('ALTER TABLE client CHANGE name name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE alias alias VARCHAR(50) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE nip nip VARCHAR(15) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE post_code post_code VARCHAR(6) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE city city VARCHAR(50) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE street street VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE country country VARCHAR(2) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE email email VARCHAR(100) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE company CHANGE name name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE nip nip VARCHAR(10) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE address address VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE post_code post_code VARCHAR(6) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE city city VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE bank_account bank_account VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE rep rep VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE lang CHANGE name name VARCHAR(50) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE short short VARCHAR(2) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE log CHANGE action action VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE `order` DROP additional_fee, CHANGE topic topic VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE state state VARCHAR(20) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE info info LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE staff CHANGE first_name first_name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE last_name last_name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE task CHANGE topic topic VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE info info LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE password password VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE first_name first_name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE last_name last_name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
