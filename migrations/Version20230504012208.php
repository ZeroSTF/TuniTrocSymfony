<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504012208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE votecomment (id INT AUTO_INCREMENT NOT NULL, id_comm INT DEFAULT NULL, iduser INT DEFAULT NULL, type INT NOT NULL, INDEX IDX_61A0C7B5232C1FE1 (id_comm), INDEX IDX_61A0C7B55E5C27E9 (iduser), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE votecomment ADD CONSTRAINT FK_61A0C7B5232C1FE1 FOREIGN KEY (id_comm) REFERENCES commentaire (id_commentaire) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE votecomment ADD CONSTRAINT FK_61A0C7B55E5C27E9 FOREIGN KEY (iduser) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE votecomment DROP FOREIGN KEY FK_61A0C7B5232C1FE1');
        $this->addSql('ALTER TABLE votecomment DROP FOREIGN KEY FK_61A0C7B55E5C27E9');
        $this->addSql('DROP TABLE votecomment');
    }
}
