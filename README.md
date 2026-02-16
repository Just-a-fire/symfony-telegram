# Symfony Telegram

## Этапы установки
1. Склонируйте проект
2. Скопируйте `.env.example` в `.env`

    ```$ cp .env.example .env```
3. Затем установите нужные значения переменных среды
4. В корневой папке проекта
```bash
docker-compose up -d --build
```

5. Установка зависимостей
```bash
docker-compose exec php sh
cd backend/
composer install
```


6. Применение миграции
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```
7. Загрузка фикстуры
```bash
php bin/console doctrine:fixtures:load --append
```

8. Запустите проект http://localhost:3030/

Порт из переменной `.env` REACT_EXTERNAL_PORT

9. Создайте заказ http://localhost:3030/shops/1/checkout

### Bot Token
```
8325481018:AAHYqMT9IAXsDie6FSI0B_A2ipN68phZRX8
```

Чтобы узнать свой **Chat ID**,  перейдите [@Getmyid_bot](https://t.me/getmyid_bot) и нажмите START

Чтобы не было ошибки `chat not found`, напишите в [@flowershopnotificationbot](https://t.me/flowershopnotificationbot)

## Запуск тестов

1. **Подготовка `backend\.env.test`**. Установите переменные `BOT_TOKEN` и `CHAT_ID` (указаны выше) из `backend\.env.test.example`
```env
BOT_TOKEN=123456:ABCDEF
CHAT_ID=987654321
```

2. **Подготовка тестовой базы данных**
```bash
php bin/console cache:clear --env=test
# если были миграции до этого
php bin/console doctrine:schema:drop --force --env=test
php bin/console doctrine:migrations:migrate --env=test
```

3. **Запуск самих тестов**
```bash
php bin/phpunit
```

## Список допущений/упрощений

1. Файлы инфрастуктуры, backend и frontend лучше разделить на разные репозитории

2. В `docker-compose.yml` настроить `depends_on` и `healthcheck` для БД

3. Использовать **многоэтапную сборку (Multi-stage build)** для сборки `Docker`-образов

4. Для **базы данных** использовать `internal`-сеть, а пробросы портов лучше вынести `docker-compose.override.yml` (который в `.gitignore`)

5. Можно использовать **API platform** 

6. Уведомления лучше отправлять в фоновом режиме (`Symfony Messenger`, `rabbitMQ`), 
чтобы не ждать ответа от **API Telegram** при оформлении заказа

7. Если нам нужно будет на PHP считать суммы заказов с копейками (хотя желательно это делать в БД), то удобно использовать расширение `ext-decimal`

8. При создании миграций, чтобы не создавать типы вручную для **PostgreSQL Native ENUM**, подойдут библиотеки `doctrine-postgresql-enum`, `doctrine-enum-bundle`