<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240520100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table reservation';
    }

    public function up(Schema $schema): void
    {
        // Création de la séquence pour l'ID de la réservation
        $this->addSql('CREATE SEQUENCE reservation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        
        // Création de la table reservation
        $this->addSql('CREATE TABLE reservation (
            id INT NOT NULL DEFAULT nextval(\'reservation_id_seq\'),
            user_id INT NOT NULL,
            place_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            number_of_guests INT NOT NULL,
            total_price DOUBLE PRECISION NOT NULL,
            status VARCHAR(255) NOT NULL,
            stripe_payment_id VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        
        // Création des index
        $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');
        $this->addSql('CREATE INDEX IDX_42C84955DA6A219 ON reservation (place_id)');
        
        // Ajout des contraintes de clé étrangère
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955DA6A219 FOREIGN KEY (place_id) REFERENCES place (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        
        // Ajout des commentaires pour les types de date
        $this->addSql('COMMENT ON COLUMN reservation.start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reservation.end_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reservation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reservation.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // Suppression de la table et de la séquence
        $this->addSql('DROP SEQUENCE reservation_id_seq CASCADE');
        $this->addSql('DROP TABLE reservation CASCADE');
    }
}
