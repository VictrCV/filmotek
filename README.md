## Trabajo Fin de Máster
# Aplicación web para la gestión de películas y series
### [Máster Universitario en Ingeniería Web de la Universidad Politécnica de Madrid](http://miw.etsisi.upm.es)

> El objetivo de este TFM es desarrollar una aplicación web que permita gestionar películas y series, aplicando para ello los conocimientos adquiridos en el Máster en Ingeniería Web.

## Estado del código
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=VictrCV_filmotek&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=VictrCV_filmotek)

### Autor
> Víctor Claramunt Velasco

### Tutor
> Francisco Javier Gil Rubio

### Tecnologías necesarias
`PHP` `GitHub` `Symfony` `Doctrine` `API REST` `SonarCloud` `Swagger`

## Instalación de la aplicación

1. Generar un esquema de base de datos vacío y un usuario/contraseña
con privilegios completos sobre dicho esquema.

2. Crear una copia del fichero `./.env` y renombrarla
como `./.env.local`. Después se debe editar dicho fichero y modificar la variable `DATABASE_URL`
con los siguientes parámetros:

* Nombre y contraseña del usuario generado anteriormente.
* Nombre del esquema de bases de datos.

3. Repetir los pasos 1 y 2 con la base de datos para las pruebas y `./.env.test`.
4. Obtener una key de la API [MoviesDatabase](https://rapidapi.com/SAdrian/api/moviesdatabase/) y escribirla 
en la variable `RAPIDAPI_KEY` de `./.env.local`.
5. Actualizar las dependencias y la base de datos mediante estos comandos:
```
composer update
php bin/console doctrine:schema:update --dump-sql --force
```

6. Configurar el token de autentificación mediante los siguientes comandos. En la instalación de XAMPP el programa *openssl* se encuentra en el directorio `XAMPP/apache/bin`.
Como *pass phrase* se empleará la especificada en la variable `JWT_PASSPHRASE` en el fichero `.env`.
```
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

## Comandos útiles
Lanzar el servidor con la aplicación en desarrollo:
```
symfony serve --no-tls [-d]
```

Lanzar todos los test y generar informe de cobertura:
```
bin/phpunit --coverage-clover=tests/coverage.xml --coverage-text
```

Analizar la calidad del código con SonarCloud. Antes se deberá haber ejecutado el comando anterior
y haber obtenido el token del proyecto en Sonar, escribiéndolo donde pone `SONAR_TOKEN`:
```
sonar-scanner.bat -Dsonar.login=SONAR_TOKEN
```

Validar la base de datos:
```
php bin/console doctrine:schema:validate
```