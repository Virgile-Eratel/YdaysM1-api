<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240520000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table review et ajout des relations';
    }

    public function up(Schema $schema): void
    {
        // Création de la table review
        $this->addSql('CREATE TABLE review (
            id SERIAL PRIMARY KEY,
            author_id INT NOT NULL,
            place_id INT NOT NULL,
            message TEXT NOT NULL,
            rating INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');

        // Création des index
        $this->addSql('CREATE INDEX IDX_794381C6F675F31B ON review (author_id)');
        $this->addSql('CREATE INDEX IDX_794381C6DA6A219 ON review (place_id)');

        // Ajout des contraintes de clé étrangère
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6DA6A219 FOREIGN KEY (place_id) REFERENCES place (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Ajout de la conversion du type datetime_immutable
        $this->addSql('COMMENT ON COLUMN review.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // Suppression des contraintes de clé étrangère et de la table review
        $this->addSql('DROP TABLE review CASCADE');
    }
}
