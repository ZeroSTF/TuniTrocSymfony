<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410075836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire CHANGE id_post id_post INT DEFAULT NULL, CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fidelite CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier CHANGE produit_s produit_s INT DEFAULT NULL, CHANGE produit_r produit_r INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY user_post');
        $this->addSql('DROP INDEX user_post ON post');
        $this->addSql('ALTER TABLE post CHANGE contenu contenu LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE produit CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE id_userS id_userS INT DEFAULT NULL, CHANGE id_userR id_userR INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE commentaire CHANGE id_post id_post INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE fidelite CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE panier CHANGE produit_r produit_r INT NOT NULL, CHANGE produit_s produit_s INT NOT NULL');
        $this->addSql('ALTER TABLE post CHANGE contenu contenu VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT user_post FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('CREATE INDEX user_post ON post (id_user)');
        $this->addSql('ALTER TABLE produit CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE id_userS id_userS INT NOT NULL, CHANGE id_userR id_userR INT NOT NULL');
    }
}
