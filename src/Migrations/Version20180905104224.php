<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180905104224 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` ADD name VARCHAR(255) NOT NULL, ADD price INT NOT NULL, ADD comment VARCHAR(255) DEFAULT NULL, ADD is_sent TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE emfdsfsdf??ail emfdsfsdfå›ail VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` DROP name, DROP price, DROP comment, DROP is_sent');
        $this->addSql('ALTER TABLE user CHANGE emfdsfsdfå›ail emfdsfsdf??ail VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
