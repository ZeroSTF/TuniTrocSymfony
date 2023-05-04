<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504003436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorieevent (id INT AUTO_INCREMENT NOT NULL, descrption VARCHAR(50) DEFAULT \'NULL\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire (id_commentaire INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, id_post INT DEFAULT NULL, contenu VARCHAR(255) NOT NULL, date DATE NOT NULL, INDEX id_post (id_post), INDEX id_user (id_user), PRIMARY KEY(id_commentaire)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id_post INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, id_categorie INT DEFAULT NULL, description VARCHAR(255) NOT NULL, date_p DATE NOT NULL, image VARCHAR(255) NOT NULL, INDEX id_categorie (id_categorie), INDEX id_user (id_user), PRIMARY KEY(id_post)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCD1AA708F FOREIGN KEY (id_post) REFERENCES post (id_post)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC9486A13 FOREIGN KEY (id_categorie) REFERENCES categorieevent (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC6B3CA4B');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCD1AA708F');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D6B3CA4B');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC9486A13');
        $this->addSql('DROP TABLE categorieevent');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE post');
    }
}
