# Telemetry 2.0 Application

## Introduction

Telemetry 2.0 is a comprehensive overhaul of the existing site designed to visualize telemetry data through graphs. This data is anonymously collected from GLPI instances and provides valuable insights into usage patterns.

## System Requirements

Before setting up the Telemetry 2.0 application, ensure that your system meets the following requirements:

- A web server (Apache, NginX, etc.)
- A database server (MariaDB, MySQL)
- PHP version 8.1 or higher
- Node.js version 18.18 or higher
- npm version 9.8.1 or higher
- Composer version 2.5.5 or higher

## Installation

Follow these steps to install the Telemetry 2.0 application:

### Node.js Dependencies

First, install all Node.js dependencies by running:

```bash
npm install
```

### PHP Dependencies

You need to enable at least those two PHP extensions:

- `pdo_mysql`
- `mbstring`

Then, install PHP dependencies by runnning:

```bash
composer install
```

### Database Configuration

Connect your database by replacing the parameters in the `DATABASE_URL` envar in the `.env` file by your owns.

### Database Setup

Create and set up the database by running: 

```bash
php bin/console doctrine:database:drop --if-exists --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Build the Application

Finally, build the app with: 

```bash
npm run build
```

### Usage

After installation, you can start the web server and access the Telemetry 2.0 application to begin visualizing telemetry data.
