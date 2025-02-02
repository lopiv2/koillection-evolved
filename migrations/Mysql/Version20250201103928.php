<?php

declare(strict_types=1);

namespace App\Migrations\Mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250201103928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE koi_feed (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, link VARCHAR(255) NOT NULL, pub_date DATETIME DEFAULT NULL, guid VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, source_id INT NOT NULL, INDEX IDX_2E1C2B7953C1C61 (source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE koi_feed_source (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE koi_feed ADD CONSTRAINT FK_2E1C2B7953C1C61 FOREIGN KEY (source_id) REFERENCES koi_feed_source (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE koi_feed DROP FOREIGN KEY FK_2E1C2B7953C1C61');
        $this->addSql('DROP TABLE koi_feed');
        $this->addSql('DROP TABLE koi_feed_source');
    }
}
