# Сборка образа
build:
	docker compose build --no-cache

# Запуск контейнера
run:
	docker compose up -d

# Остановка контейнера
stop:
	docker compose stop

# Удаление контейнера
rm:
	docker compose down

# Полная перезагрузка (остановка, удаление и запуск)
restart:
	docker compose restart

app:
	docker compose exec php bash

migration:
	docker compose exec php php artisan migrate

rollback:
	docker compose exec php php artisan migrate:rollback

test:
	docker compose exec php php artisan test