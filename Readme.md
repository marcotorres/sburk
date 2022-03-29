# Proyecto SBURK

SCHOOL BUS TRACKER FOR SCHOOLS, BUS DRIVERS, PARENTS

SBurK is a comprehensive SaaS school bus tracker system for managing the daily school bus operation that links the school, the parents and the buses into one powerful system that contains

## Requisitos

* docker 20.10
* docker-composer 1.29 


## Instalación

Agrega la siguiente configuración en tu archivo /etc/hosts
    
    127.0.0.1   sburk.localhost

Ejecuta:

    make setup_project

## Desarrollo

El proyecto se basa en Laravel 6, localmente el desarrollo se base con docker y podras realizar procesos 
basico como:

- **make cli**: para poder ingresar al contenedor del proyecto
- **make restart**: si queremos reiniciar los contenedores
- **make stop**: para detener todo el proyecto
- **make start**: para iniciar todo el proyecto

Los ambientes del proyecto son:

- Local: http://sburk.localhost

## Instalación manual

# clonar repositorio
    git clone https://github.com/marcotorres/sburk.git

# crear .env files
    cp .env.example .env
    cp ./src/.env.example ./src/.env

# levantar contenedores
    docker-compose up -d

# downgrade a composer
    docker exec -t sburk sh -c "composer self-update 1.10.22"

# instalar dependencias (vendor)
    docker exec -t sburk sh -c "composer install"

# instalar dependencias nodejs
    docker run -ti --rm -v ${PWD}/src:/app -w /app node:latest sh -c "npm i"

# configurar usuario de mysql
    make cli_db
    docker exec -ti mysql sh
    mysql -u root -pexito    
    DROP USER usr_sburk@'%';
    CREATE USER usr_sburk@'%' IDENTIFIED WITH mysql_native_password BY 'exito';
    GRANT ALL PRIVILEGES ON schoolbustracker_db.* TO usr_sburk@'%';
    FLUSH PRIVILEGES;

# configurar laravel
    make cli    
    docker exec -ti sburk sh    
    php artisan key:generate
    php artisan migrate
    php artisan db:seed
    php artisan cache:clear
    php artisan config:clear
    php artisan view:clear
