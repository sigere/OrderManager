<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210410092653 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, order_id INT DEFAULT NULL, client_id INT DEFAULT NULL, created_at DATETIME NOT NULL, action VARCHAR(100) NOT NULL, INDEX IDX_8F3F68C5A76ED395 (user_id), INDEX IDX_8F3F68C58D9F6D38 (order_id), INDEX IDX_8F3F68C519EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, author_id INT NOT NULL, staff_id INT NOT NULL, base_lang_id INT NOT NULL, target_lang_id INT NOT NULL, deleted_at DATETIME DEFAULT NULL, certified TINYINT(1) NOT NULL, pages NUMERIC(10, 2) DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, topic VARCHAR(255) NOT NULL, state VARCHAR(20) NOT NULL, info LONGTEXT NOT NULL, adoption DATETIME NOT NULL, deadline DATETIME NOT NULL, settled_at DATETIME DEFAULT NULL, INDEX IDX_F529939819EB6921 (client_id), INDEX IDX_F5299398F675F31B (author_id), INDEX IDX_F5299398D4D57CD (staff_id), INDEX IDX_F52993983F2786C0 (base_lang_id), INDEX IDX_F5299398C04986CF (target_lang_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C58D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C519EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398D4D57CD FOREIGN KEY (staff_id) REFERENCES staff (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993983F2786C0 FOREIGN KEY (base_lang_id) REFERENCES lang (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398C04986CF FOREIGN KEY (target_lang_id) REFERENCES lang (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C58D9F6D38');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE `order`');
    }
}
