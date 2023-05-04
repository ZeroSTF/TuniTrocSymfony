<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504001717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE echange (id INT AUTO_INCREMENT NOT NULL, id_panier INT NOT NULL, etat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_d DATE NOT NULL, date_f DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fidelite (id INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, valeur INT NOT NULL, INDEX user_fidelite (id_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, produit_r INT DEFAULT NULL, produit_s INT DEFAULT NULL, date DATE NOT NULL, transporteurB TINYINT(1) DEFAULT NULL, INDEX produitR_user (produit_r), INDEX produitS_user (produit_s), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, photo VARCHAR(500) NOT NULL, ville VARCHAR(255) NOT NULL, id_user INT NOT NULL, INDEX user_produit (id_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, cause VARCHAR(255) NOT NULL, etat TINYINT(1) NOT NULL, id_userR INT DEFAULT NULL, id_userS INT DEFAULT NULL, INDEX userS_reclamation (id_userS), INDEX userR (id_userR), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transporteur (id INT AUTO_INCREMENT NOT NULL, id_echange INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, num_tel INT NOT NULL, photo MEDIUMBLOB NOT NULL, INDEX echange_transporteur (id_echange), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, pwd VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, num_tel VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, valeur_fidelite INT NOT NULL, role TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, date DATETIME NOT NULL, etat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fidelite ADD CONSTRAINT FK_EF425B236B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2B18876E7 FOREIGN KEY (produit_r) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2C68F4671 FOREIGN KEY (produit_s) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404B66FF487 FOREIGN KEY (id_userR) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404C168C411 FOREIGN KEY (id_userS) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transporteur ADD CONSTRAINT FK_A25649756BEA4ACF FOREIGN KEY (id_echange) REFERENCES echange (id)');
        $this->addSql('DROP TABLE messenger_messages');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, headers LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE fidelite DROP FOREIGN KEY FK_EF425B236B3CA4B');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2B18876E7');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2C68F4671');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404B66FF487');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404C168C411');
        $this->addSql('ALTER TABLE transporteur DROP FOREIGN KEY FK_A25649756BEA4ACF');
        $this->addSql('DROP TABLE echange');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE fidelite');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE transporteur');
        $this->addSql('DROP TABLE user');
    }
}
