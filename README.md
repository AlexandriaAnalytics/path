# Sinapsis

## Requerimientos del sistema

![10.x](https://img.shields.io/badge/Laravel-7.4-brightgreen)
![8.3](https://img.shields.io/badge/PHP-8.3-brightgreen)
![Latest](https://img.shields.io/badge/Composer-Latest-brightgreen)
![Latest](https://img.shields.io/badge/Node.js-Latest-brightgreen)

## Instalación

1.  Clonar repositorio.

```
git clone https://github.com/mayocca/sinapsis-path.git
```

1.  Instalar dependencias.

```bash
composer install
npm install
npm run dev
```

1.  Crear el archivo de las variables de entorno `.env` en la raiz del proyecto (usar la plantilla de ejemplo `.env.example` que describe todas las variables y valores posibles)

2.  Modificar las variables de entorno del archivo `.env` segun su entorno de desarrollo Ej

```bash
DB_DATABASE=sinapsis
DB_USERNAME=root
DB_PASSWORD=MY_SECRET_PASSWORD
DB_PORT=3306
```

3.  Ejecutar las migraciones (esto generara las tablas y relaciones de la base de datos) y cargarlas con informacion (seed)

```bash
php artisan migrate:fresh --seed
```

4.  Iniciar Servidor

```bash
php artisan serve
```

### Desarrollar en DevContainer

#### Requierimientos

-   Docker
-   Visual Studio Code

1. Abrir el proyecto en el editor Visual Code.
1. Descargar los complementos de `Dev Containers` y `Remote Development` de microsoft en el administador de extensiones de VS.
1. Presione la tecla `F1` y escriba `contenedores` y seleccione la opcion `Abrir carpeta en contenedor` o `volver a abrir contenedor` si ya fue creado
1. siga los pasos de instalacion clasicos desde el punto 2 en adelante.
