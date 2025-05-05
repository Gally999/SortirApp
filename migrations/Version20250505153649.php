<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250505153649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE annulation (id INT AUTO_INCREMENT NOT NULL, sortie_id INT NOT NULL, raison VARCHAR(250) NOT NULL, UNIQUE INDEX UNIQ_26F7D84CC72D953 (sortie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE annulation ADD CONSTRAINT FK_26F7D84CC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE annulation DROP FOREIGN KEY FK_26F7D84CC72D953
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE annulation
        SQL);
    }
}
