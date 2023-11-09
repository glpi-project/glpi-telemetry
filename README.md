# Telemetry 2.0 Application

## Introduction

Telemetry 2.0 is a comprehensive overhaul of the existing site designed to visualize telemetry data through graphs.
This data is anonymously collected from GLPI instances and provides valuable insights into usage patterns.

## System Requirements

Before setting up the Telemetry 2.0 application, ensure that your system meets the following requirements:

- A web server (Apache, NginX, etc.)
- A database server (MariaDB, MySQL)
- PHP version 8.1 or higher
- Node.js version 18.18 or higher
- npm version 9.8.1 or higher
- Composer version 2.5.5 or higher

## Docker development environment

A docker development environment can be easilly instanciated by running the command `docker compose up -d`.

By default, the HTTP port is not exposed, to prevent conflicts with other projects. You will have to define it in
the `docker-compose.override.yml` file.

```yaml
services:
  telemetry.glpi-project.org:
    ports:
      - "8001:80"
```

The default uid/gid used by the docker container is `1000`. If your host user uses different uid/gid, you may encounter
file permissions issues. To prevent this, you can customize them using the corresponding build args in
the `docker-compose.override.yml` file.

```yaml
services:
  telemetry.glpi-project.org:
    build:
      args:
        HOST_GROUP_ID: "${HOST_GROUP_ID:-1000}"
        HOST_USER_ID: "${HOST_USER_ID:-1000}"
```

By default, only the application container is created. If you want to create additional services, you can add them in
the `docker-compose.override.yml` file.

```yaml
services:
  telemetry.glpi-project.org:
    restart: "unless-stopped"
    ports:
      - "8001:80"
  database:
    image: "mariadb:11.0"
    environment:
      MYSQL_ROOT_PASSWORD: "R00tP4ssw0rd"
      MYSQL_DATABASE: "telemetry"
      MYSQL_USER: "telemetry"
      MYSQL_PASSWORD: "P4ssw0rd"
    ports:
      - "3306:3306"
    volumes:
      - "db:/var/lib/mysql"
  mailer:
    image: "schickling/mailcatcher"
    ports:
      - "1025"
      - "1080:1080"

volumes:
  db:
```

Corresponding services could be used by defining the following variables in the `.env.local` file:
 - `DATABASE_URL="mysql://telemetry:P4ssw0rd@database:3306/telemetry?charset=utf8mb4"`;
 - `MAILER_DSN=smtp://mailer:1025`.

## Installation

Follow these steps to install the Telemetry 2.0 application:

- Create a `.env.local` file using the `.env.example` file and update the variables according to your environment.
- Check that required PHP extensions are installed using the `composer check-platform-reqs` command.
- Install the application dependencies using the `composer install` and the `npm install` commands.
- If the database does not exists, create it using the `php bin/console doctrine:database:create` command.
- Initialize the database structure using the `php bin/console doctrine:migrations:migrate` command.
- Build the application assets using the `npm run build` command.

## Usage

After installation, you can start the web server and access the Telemetry 2.0 application to begin visualizing telemetry data.
