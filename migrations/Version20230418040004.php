<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230418040004 extends AbstractMigration
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
        $this->addSql('ALTER TABLE echange DROP FOREIGN KEY fk_transporteur_echange');
        $this->addSql('ALTER TABLE echange DROP FOREIGN KEY fk_panier_echange');
        $this->addSql('DROP INDEX fk_transporteur_echange ON echange');
        $this->addSql('DROP INDEX fk_panier_echange ON echange');
        $this->addSql('ALTER TABLE echange DROP id_transporteur');
        $this->addSql('ALTER TABLE fidelite CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier CHANGE produit_s produit_s INT DEFAULT NULL, CHANGE produit_r produit_r INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY user_produit');
        $this->addSql('ALTER TABLE reclamation CHANGE id_userS id_userS INT DEFAULT NULL, CHANGE id_userR id_userR INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transporteur ADD id_echange INT DEFAULT NULL, CHANGE photo photo MEDIUMBLOB NOT NULL');
        $this->addSql('ALTER TABLE transporteur ADD CONSTRAINT FK_A25649756BEA4ACF FOREIGN KEY (id_echange) REFERENCES echange (id)');
        $this->addSql('CREATE INDEX echange_transporteur ON transporteur (id_echange)');
        $this->addSql('ALTER TABLE user CHANGE photo photo VARCHAR(255) NOT NULL, CHANGE num_tel num_tel INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE commentaire CHANGE id_post id_post INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE echange ADD id_transporteur INT DEFAULT NULL');
        $this->addSql('ALTER TABLE echange ADD CONSTRAINT fk_transporteur_echange FOREIGN KEY (id_transporteur) REFERENCES transporteur (id)');
        $this->addSql('ALTER TABLE echange ADD CONSTRAINT fk_panier_echange FOREIGN KEY (id_panier) REFERENCES panier (id)');
        $this->addSql('CREATE INDEX fk_transporteur_echange ON echange (id_transporteur)');
        $this->addSql('CREATE INDEX fk_panier_echange ON echange (id_panier)');
        $this->addSql('ALTER TABLE fidelite CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE panier CHANGE produit_r produit_r INT NOT NULL, CHANGE produit_s produit_s INT NOT NULL');
        $this->addSql('ALTER TABLE post CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT user_produit FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation CHANGE id_userR id_userR INT NOT NULL, CHANGE id_userS id_userS INT NOT NULL');
        $this->addSql('ALTER TABLE transporteur DROP FOREIGN KEY FK_A25649756BEA4ACF');
        $this->addSql('DROP INDEX echange_transporteur ON transporteur');
        $this->addSql('ALTER TABLE transporteur DROP id_echange, CHANGE photo photo MEDIUMBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE photo photo LONGBLOB DEFAULT NULL, CHANGE num_tel num_tel VARCHAR(255) NOT NULL');
    }
}
