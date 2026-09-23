<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user and task tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (
                id BLOB NOT NULL --(DC2Type:uuid)
                , email VARCHAR(180) NOT NULL
                , roles CLOB NOT NULL --(DC2Type:json)
                , password VARCHAR(255) NOT NULL
                , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
                , PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_EMAIL ON "user" (email)');

        $this->addSql(<<<'SQL'
            CREATE TABLE task (
                id BLOB NOT NULL --(DC2Type:uuid)
                , title VARCHAR(255) NOT NULL
                , description CLOB DEFAULT NULL
                , is_done BOOLEAN NOT NULL
                , priority VARCHAR(10) NOT NULL
                , due_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
                , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
                , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
                , owner_id BLOB NOT NULL --(DC2Type:uuid)
                , PRIMARY KEY(id)
                , CONSTRAINT FK_TASK_OWNER FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_TASK_OWNER ON task (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE "user"');
    }
}
