<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180517123541 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE geocode (id INT NOT NULL, brewery_id INT DEFAULT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, accuracy VARCHAR(255) NOT NULL, INDEX IDX_C6773CE4D15C960 (brewery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE brewery (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, address1 VARCHAR(255) DEFAULT NULL, address2 VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(255) NOT NULL, code VARCHAR(10) NOT NULL, country VARCHAR(50) NOT NULL, phone VARCHAR(50) NOT NULL, website VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, last_modification DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE beer (id INT NOT NULL, brewery_id INT DEFAULT NULL, category_id INT DEFAULT NULL, style_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, abv DOUBLE PRECISION NOT NULL, ibu DOUBLE PRECISION NOT NULL, srm DOUBLE PRECISION NOT NULL, upc VARCHAR(12) NOT NULL, description LONGTEXT DEFAULT NULL, last_modification DATETIME NOT NULL, INDEX IDX_58F666ADD15C960 (brewery_id), INDEX IDX_58F666AD12469DE2 (category_id), INDEX IDX_58F666ADBACD6074 (style_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, last_modification DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE style (id INT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, last_modification DATETIME NOT NULL, INDEX IDX_33BDB86A12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE geocode ADD CONSTRAINT FK_C6773CE4D15C960 FOREIGN KEY (brewery_id) REFERENCES brewery (id)');
        $this->addSql('ALTER TABLE beer ADD CONSTRAINT FK_58F666ADD15C960 FOREIGN KEY (brewery_id) REFERENCES brewery (id)');
        $this->addSql('ALTER TABLE beer ADD CONSTRAINT FK_58F666AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE beer ADD CONSTRAINT FK_58F666ADBACD6074 FOREIGN KEY (style_id) REFERENCES style (id)');
        $this->addSql('ALTER TABLE style ADD CONSTRAINT FK_33BDB86A12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE geocode DROP FOREIGN KEY FK_C6773CE4D15C960');
        $this->addSql('ALTER TABLE beer DROP FOREIGN KEY FK_58F666ADD15C960');
        $this->addSql('ALTER TABLE beer DROP FOREIGN KEY FK_58F666AD12469DE2');
        $this->addSql('ALTER TABLE style DROP FOREIGN KEY FK_33BDB86A12469DE2');
        $this->addSql('ALTER TABLE beer DROP FOREIGN KEY FK_58F666ADBACD6074');
        $this->addSql('DROP TABLE geocode');
        $this->addSql('DROP TABLE brewery');
        $this->addSql('DROP TABLE beer');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE style');
    }
}
