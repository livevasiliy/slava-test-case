# **Проект: Импорт данных с использованием Laravel**

## **Описание проекта**
Этот проект реализует систему импорта данных из файлов (например, Excel или CSV) в базу данных PostgreSQL. Проект использует:
- **Laravel** для управления бизнес-логикой.
- **Supervisor** для обработки фоновых задач (очередей).
- **Redis** для очередей.
- **Nginx** в качестве веб-сервера.
- **Docker** для контейнеризации приложения.

Система поддерживает:
- Валидацию данных.
- Асинхронную обработку больших файлов через очереди.
- Настройку параметров импорта (размер пакета, использование очередей, номер строки заголовка).

---

## **Требования**
Для работы с проектом вам потребуется:
- Docker и Docker Compose.
- PHP 8.3.
- Node.js (для сборки фронтенда).
- База данных PostgreSQL.
- Redis.

---

## **Установка и настройка**

### 1. **Клонирование репозитория**
Склонируйте репозиторий на ваш компьютер:
```bash
git clone https://github.com/livevasiliy/slava-test-case.git
cd slava-test-case
```

### 2. **Настройка переменных окружения**
Создайте файл `.env` на основе `.env.example`:
```bash
cp .env.example .env
```
Заполните следующие переменные:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=app_db
DB_USERNAME=user
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PORT=6379
```

### 3. **Сборка и запуск контейнеров**
Соберите и запустите контейнеры с помощью Docker Compose:
```bash
docker-compose up --build
```

### 4. **Установка зависимостей**
После запуска контейнеров установите зависимости PHP и Node.js:
```bash
docker-compose exec php composer install
docker-compose exec php npm install
```

### 5. **Генерация ключа приложения**
Сгенерируйте ключ приложения Laravel:
```bash
docker-compose exec php php artisan key:generate
```

### 6. **Миграции и сиды**
Выполните миграции базы данных:
```bash
docker-compose exec php php artisan migrate
```
Если необходимо, запустите сиды для наполнения базы тестовыми данными:
```bash
docker-compose exec php php artisan db:seed
```

### 7. **Сборка фронтенда**
Соберите статические файлы фронтенда:
```bash
docker-compose exec php npm run build
```

---

## **Использование**

### 1. **Импорт данных**
Для импорта данных отправьте POST-запрос на эндпоинт `/import` с файлом:
```bash
curl -X POST http://localhost:8080/import \
    -F "file=@/path/to/your/file.xlsx"
```

### 2. **Просмотр логов**
Логи приложения находятся в директории `storage/logs`. Вы можете просмотреть их с помощью команды:
```bash
docker-compose exec php tail -f storage/logs/laravel.log
```

### 3. **Статус очередей**
Проверьте статус фоновых задач (очередей):
```bash
docker-compose exec php php artisan queue:work
```
Или проверьте Supervisor:
```bash
docker-compose exec php supervisorctl status
```

---

## **Структура проекта**

```
.
├── app/                 # Основная логика приложения
   ├── Exceptions/      # Классы для исключений
   ├── FileReaders/     # Классы для чтения файлов
   ├── Http/            # Классы для работы с Http запросами
   ├── Imports/         # Классы для работы с импортом
   ├── Jobs/            # Классы для фоновых задач (Laravel Jobs)
   ├── Logger/          # Классы для собственных логгеров
   ├── Models/          # Классы моделей
   ├── Providers/       # Классы Service Provider
   ├── Validators/      # Собственные валидаторы данных для импорта
├── config/                  # Конфигурационные файлы Laravel
├── database/                # Миграции и сиды
├── docker/                  # Конфигурация Docker
    ├── nginx/           # Файлы конфигурация Nginx   
        ├── nginx.conf           # Основная конфигурация Nginx
    ├── supervisord/           # Файлы конфигурация Supervisor
        ├── supervisord.conf     # Конфигурация Supervisor
    ├── php-fpm/           # Файлы конфигурация php-fpm
        ├── Dockerfile     # Dockerfile php-fpm контейнера
├── public/                  # Публичные файлы
├── resources/               # Шаблоны Blade и assets
├── routes/                  # Маршруты
├── storage/                 # Логи и временные файлы
└── .env                     # Переменные окружения
```

---

## **Тестирование**

### 1. **Unit-тесты**
Запустите тесты PHPUnit:
```bash
docker-compose exec php php artisan test
```

### 2. **Тестирование API**
Используйте Postman или curl для тестирования endpoint.

---

## **Лицензия**
Этот проект распространяется под лицензией [MIT](https://opensource.org/licenses/MIT). Подробности см. в файле [LICENSE](LICENSE).

---

## **Авторы**
- Василий Пивоваров ([GitHub](https://github.com/livevasiliy))
