<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504194955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY user_com');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY post_com');
        $this->addSql('DROP INDEX post_com ON commentaire');
        $this->addSql('CREATE INDEX id_post ON commentaire (id_post)');
        $this->addSql('DROP INDEX user_com ON commentaire');
        $this->addSql('CREATE INDEX id_user ON commentaire (id_user)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT user_com FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT post_com FOREIGN KEY (id_post) REFERENCES post (id_post)');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY user_post');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC9486A13 FOREIGN KEY (id_categorie) REFERENCES categorieevent (id)');
        $this->addSql('DROP INDEX user_post ON post');
        $this->addSql('CREATE INDEX id_user ON post (id_user)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT user_post FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE votecomment CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC6B3CA4B');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCD1AA708F');
        $this->addSql('DROP INDEX id_post ON commentaire');
        $this->addSql('CREATE INDEX post_com ON commentaire (id_post)');
        $this->addSql('DROP INDEX id_user ON commentaire');
        $this->addSql('CREATE INDEX user_com ON commentaire (id_user)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCD1AA708F FOREIGN KEY (id_post) REFERENCES post (id_post)');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC9486A13');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D6B3CA4B');
        $this->addSql('DROP INDEX id_user ON post');
        $this->addSql('CREATE INDEX user_post ON post (id_user)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE votecomment CHANGE id id INT NOT NULL');
    }
}
