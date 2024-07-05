# SAHL Web Management Application

## System Requirements
Ensure you have Docker and Docker Compose installed on a clean Ubuntu server 22.04.

Required packages:

    sudo apt-get update
    sudo apt-get install docker.io docker-compose

## Project Setup

1. **Clone the Repository:**
   
   Clone the project repository to your local machine.

    ```bash
    git clone -b master git@bitbucket.org:tandie/sahl.git /path/to/your/local/sahl
    cd /path/to/your/local/sahl
    ```

2. **Create Docker Configuration Files:**

    Create a `Dockerfile` in the project root with the following content:

    ```Dockerfile
    FROM php:8.2-apache

    # Install necessary PHP extensions
    RUN docker-php-ext-install pdo pdo_mysql mysqli

    # Enable Apache mod_rewrite and SSL
    RUN a2enmod rewrite
    RUN a2enmod ssl

    # Copy application source
    COPY . /var/www/html/

    # Set the correct permissions
    RUN chown -R www-data:www-data /var/www/html
    RUN chmod -R 755 /var/www/html

    # Expose port 80
    EXPOSE 80
    ```

    Create a `docker-compose.yml` file in the project root with the following content:

    ```yaml
    version: '3.8'

    services:
      web:
        build: .
        ports:
          - "80:80"
        volumes:
          - .:/var/www/html
        environment:
          MYSQL_HOST: db
          MYSQL_USER: root
          MYSQL_PASSWORD: example
          MYSQL_DB: sahl
        depends_on:
          - db

      db:
        image: mysql:8.0
        restart: always
        environment:
          MYSQL_ROOT_PASSWORD: example
          MYSQL_DATABASE: sahl
        ports:
          - "3306:3306"
        volumes:
          - db_data:/var/lib/mysql

    volumes:
      db_data:
    ```

3. **Build and Run Containers:**

    In the project root directory, build and start the containers:

    ```bash
    sudo docker-compose up --build -d
    ```

4. **Configure Hosts File:**

    Edit the `/etc/hosts` file and add the following line, where `X` is any unused number from 0 - 255:

    ```bash
    127.0.0.X sahl
    ```

    Access the application in your browser at `http://sahl/`.

5. **Database Setup:**

    Import your database using a tool like phpMyAdmin or directly through MySQL CLI inside the `db` container.

    ```bash
    sudo docker exec -it <db_container_id> mysql -u root -p sahl < /path/to/your/database.sql
    ```

6. **Local Configuration:**

    Add your `config.local.neon` to the `app/config` directory inside the `web` container.

    ```bash
    sudo docker exec -it <web_container_id> bash
    cd /var/www/html/app/config
    nano config.local.neon
    ```

## Updating the Application

To update the application with the latest changes from the repository:

1. Pull the latest changes:

    ```bash
    git pull
    ```

2. Rebuild and restart the containers:

    ```bash
    sudo docker-compose up --build -d
    ```

3. Clear the cache:

    ```bash
    sudo docker exec -it <web_container_id> rm -r /var/www/html/temp/cache
    ```

## Useful Docker Commands

- **Start Containers:**

    ```bash
    sudo docker-compose up -d
    ```

- **Stop Containers:**

    ```bash
    sudo docker-compose down
    ```

- **View Logs:**

    ```bash
    sudo docker-compose logs -f
    ```

- **Access Shell in Web Container:**

    ```bash
    sudo docker exec -it <web_container_id> bash
    ```
    
