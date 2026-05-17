# Wildberries Data Fetcher

## О проекте

Приложение для загрузки данных из API Wildberries (продажи, заказы, склады, доходы) и сохранения их в базу данных.

# КЛЮЧЕВЫЕ ФАЙЛЫ ПРОЕКТА

Маршрутизация реализована в файле: routes\web.php

Разработанный контроллер находится в папке: app\Http\Controllers
(FetchDataController)

Файлы миграции находятся в директории: database\migrations

Разработанные модели находятся: app\Models

Файлы ресурсов находятся в директории: public\

# ИНСТАЛЯЦИЯ

Склонируйте проект в директорию с сервером:

`git clone https://github.com/AlbertKim15/testExercise.git`

Затем, открыв из папки проекта консоль, введите команду для установки пакетов ларавель:

`composer install`

Создайте базу данных на сервере и заполните поля файла .env, находящийся в папке проекта по примеру:

`DB_CONNECTION=mysql`

`DB_HOST=127.0.0.1`

`DB_PORT=3306`

`DB_DATABASE=backendTest`

`DB_USERNAME=root`

`DB_PASSWORD=null`

В открытой консоли директории проекта введите команду для генерации таблиц базы данных:

`php artisan migrate`

В той же консоли для запуска сайта по адресу `http://localhost:8000` введите команду:

`php artisan serve`

Откройте сайт в браузере по адресу  `http://localhost:8000`

## Настройка .env файла на хостинге 

APP_ENV=production
APP_DEBUG=false
APP_URL=http://ваш-сайт.ru

DB_HOST=localhost
DB_DATABASE=ваш_логин_имя_базы
DB_USERNAME=ваш_логин_пользователь
DB_PASSWORD=ваш_пароль

## Принцип работы 

Сайт стоит на бесплатном хостинге sprinthost
У него есть графический интерфейс но при этом все команды связные с пользование работают 
Команда: 
php artisan wb:fetch --dateFrom=датаОт(YYYY-MM-DD) --dateTo=датаДо(YYYY-MM-DD)

Так же у сайта есть графический интерфейс для самого хостинга т.к. на этом хостинге нет доступа по SSH ключу
Ссылка: 
http://f1268349.xsph.ru/

