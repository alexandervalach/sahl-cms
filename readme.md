# SAHL CMS

SAHL CMS is a web management application for hockey players, teams, and competitions used by the Slovak Amateur Hockey League.

The application is built with **PHP (Nette Framework)** and is containerized using **Docker** for local development and deployment.

---

## Features

1. Player management
2. Team and roster administration
3. League and competition management
4. Match results and statistics
5. Administrative backend

---

## Technology Stack

1. PHP
2. Nette Framework
3. Nginx
4. PostgreSQL
5. Docker & Docker Compose
6. Composer
7. pgAdmin


## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/alexandervalach/sahl-cms.git
cd sahl-cms
```

---

### 2. Environment Configuration

Create a `.env` file in the project root:

```env
# Web
WEB_PORT=8080
APACHE_RUN_USER=www-data
APACHE_RUN_GROUP=www-data
# Development for Debug
NETTE_DEBUG=1

# Database
DB_HOST=db
DB_PORT=5432
DB_USER=sahl
# Update the password
DB_PASSWORD=secret
DB_NAME=sahl

# PostgreSQL
POSTGRES_PORT=5432

# pgAdmin
PGADMIN_PORT=5050
PGADMIN_DEFAULT_EMAIL=admin@sample.com
# Update the password
PGADMIN_DEFAULT_PASSWORD=admin
```

---

### 3. Build and Start Containers

```bash
docker-compose up --build -d
```

The application will be available at:

```
http://localhost:8080
```

pgAdmin (optional) will be available at:

```
http://localhost:5050
```

---

## Database

### Automatic Initialization

Any `.sql` files placed in the `/db` directory will be executed automatically when the PostgreSQL container is created for the first time.

---

### Manual Import

```bash
docker exec -i postgres psql \
  -U sahl \
  -d sahl < database.sql
```

---

## Application Configuration

### Local Configuration File

Create:

```
app/config/config.local.neon
```

Example:

```neon
database:
    dsn: "pgsql:host=db;port=5432;dbname=sahl"
    user: %env.DB_USER%
    password: %env.DB_PASSWORD%

parameters:
    debugMode: true
```

---

## Writable Directories

The following directories must be writable by the web server:

```
/log
/temp
```

These are mapped to Docker volumes:

* `log_data`
* `temp_data`

No manual permission changes are required.

---

## Development Workflow

### Updating the Application

```bash
git pull
docker-compose up --build -d
```

### Clearing Cache

```bash
docker exec -it sahl-cms rm -rf /var/www/html/temp/cache
```

---

## Useful Docker Commands

**View logs**

```bash
docker-compose logs -f
```

**Access web container**

```bash
docker exec -it sahl-cms bash
```

**Stop containers**

```bash
docker-compose down
```

---

## Docker Services

### Web

1. PHP with Nginx
2. Nette application
3. Composer dependencies installed during image build

### Database

1. PostgreSQL
2. Persistent storage via `pgdata` volume

### pgAdmin

1. Web-based PostgreSQL administration tool
2. Intended for development use
