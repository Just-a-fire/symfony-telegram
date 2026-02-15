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

8. Запустите проект http://localhost:3031/

Порт из переменной `.env` REACT_EXTERNAL_PORT

9. Создайте заказ http://localhost:3003/shops/1/checkout

### Bot Token
8325481018:AAHYqMT9IAXsDie6FSI0B_A2ipN68phZRX8

Чтобы узнать свой **Chat ID**,  перейдите [@Getmyid_bot](https://t.me/getmyid_bot) и нажмите START

Чтобы не было ошибки  `chat not found` напишите в `@flowershopnotificationbot`