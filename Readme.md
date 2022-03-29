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
