<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240509180438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enigme ADD reponse_a VARCHAR(255) NOT NULL, ADD reponse_b VARCHAR(255) NOT NULL, ADD reponse_c VARCHAR(255) NOT NULL, ADD reponse_d VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enigme DROP reponse_a, DROP reponse_b, DROP reponse_c, DROP reponse_d');
    }
}
