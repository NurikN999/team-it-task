# Team IT Test Task

Веб-приложение на Symfony с PostgreSQL базой данных и Nginx веб-сервером, развернутое в Docker контейнерах.

## 🚀 Технологии

- **Backend**: Symfony 7.3 (PHP 8.3)
- **База данных**: PostgreSQL 16
- **Веб-сервер**: Nginx
- **Контейнеризация**: Docker & Docker Compose
- **ORM**: Doctrine
- **API**: REST API

## 📋 Требования

- Docker
- Docker Compose
- Git

## 🛠 Установка и запуск

### 1. Клонирование репозитория
```bash
git clone <repository-url>
cd team_it_test_task
```

### 2. Сборка и запуск контейнеров
```bash
# Сборка Docker образов
./docker-commands.sh build

# Запуск всех сервисов
./docker-commands.sh up
```

### 3. Выполнение миграций базы данных
```bash
# Запуск миграций
./docker-commands.sh migrate
```

### 4. Доступ к приложению
- **Веб-приложение**: http://localhost:8080
- **База данных**: localhost:5432
- **Mailpit (почта)**: http://localhost:57155

## 📖 Доступные команды

Используйте скрипт `docker-commands.sh` для управления проектом:

```bash
# Основные команды
./docker-commands.sh build          # Сборка контейнеров
./docker-commands.sh up             # Запуск сервисов
./docker-commands.sh down           # Остановка сервисов
./docker-commands.sh restart        # Перезапуск сервисов

# Логи
./docker-commands.sh logs           # Показать все логи
./docker-commands.sh symfony-logs   # Логи Symfony
./docker-commands.sh nginx-logs     # Логи Nginx
./docker-commands.sh db-logs        # Логи базы данных

# Работа с контейнерами
./docker-commands.sh shell          # Войти в Symfony контейнер
./docker-commands.sh db-shell       # Войти в базу данных

# Symfony команды
./docker-commands.sh migrate        # Запустить миграции
./docker-commands.sh cache-clear    # Очистить кэш
./docker-commands.sh composer-install  # Установить зависимости
```

## 🔧 Конфигурация

### Переменные окружения
Создайте файл `.env` в корне проекта:

```env
# База данных
POSTGRES_VERSION=16
POSTGRES_DB=app
POSTGRES_USER=app
POSTGRES_PASSWORD=your_secure_password

# Symfony
APP_ENV=dev
APP_SECRET=your-secret-key-here
DATABASE_URL="postgresql://app:your_secure_password@database:5432/app?serverVersion=16&charset=utf8"
```

### Структура проекта
```
.
├── src/                    # Исходный код приложения
├── config/                 # Конфигурация Symfony
├── migrations/             # Миграции базы данных
├── public/                 # Публичная директория
├── templates/              # Twig шаблоны
├── tests/                  # Тесты
├── docker/                 # Docker конфигурация
│   └── nginx/             # Nginx конфигурация
├── compose.yaml           # Docker Compose конфигурация
├── Dockerfile             # Docker образ для Symfony
└── docker-commands.sh     # Скрипт управления
```

## 📚 API Документация

### Получение API документации

1. **Swagger/OpenAPI документация**:
   - Откройте браузер и перейдите по адресу: http://localhost:8080/api/doc
   - Или используйте Swagger UI: http://localhost:8080/api/doc.json

2. **Nelmio API Doc Bundle** (если установлен):
   - Документация: http://localhost:8080/api/doc
   - JSON формат: http://localhost:8080/api/doc.json

3. **Ручная проверка API**:
   ```bash
   # Пример GET запроса
   curl http://localhost:8080/api/endpoint
   
   # Пример POST запроса
   curl -X POST http://localhost:8080/api/endpoint \
     -H "Content-Type: application/json" \
     -d '{"key": "value"}'
   ```

### Основные API endpoints

Для получения полного списка доступных endpoints:

```bash
# Войти в Symfony контейнер
./docker-commands.sh shell

# Показать все маршруты
php bin/console debug:router
```

## 🗄 Работа с базой данных

### Подключение к базе данных
```bash
# Через Docker команду
./docker-commands.sh db-shell

# Или напрямую
docker-compose exec database psql -U app -d app
```

### Управление миграциями
```bash
# Создать новую миграцию
docker-compose exec symfony php bin/console make:migration

# Запустить миграции
./docker-commands.sh migrate

# Статус миграций
docker-compose exec symfony php bin/console doctrine:migrations:status
```

## 🐛 Отладка

### Просмотр логов
```bash
# Все логи
./docker-commands.sh logs

# Конкретного сервиса
./docker-commands.sh symfony-logs
./docker-commands.sh nginx-logs
./docker-commands.sh db-logs
```

### Очистка кэша
```bash
./docker-commands.sh cache-clear
```

### Пересборка контейнеров
```bash
# Остановить и удалить контейнеры
./docker-commands.sh down

# Пересобрать и запустить
./docker-commands.sh build
./docker-commands.sh up
```

## 🔒 Безопасность

### Для продакшена
1. Измените все пароли по умолчанию
2. Настройте HTTPS в Nginx
3. Используйте переменные окружения для секретных данных
4. Настройте файрвол
5. Регулярно обновляйте зависимости

## 📝 Разработка

### Добавление новых функций
1. Создайте новую ветку: `git checkout -b feature/new-feature`
2. Внесите изменения в код
3. Создайте миграцию если нужно: `docker-compose exec symfony php bin/console make:migration`
4. Запустите миграции: `./docker-commands.sh migrate`
5. Протестируйте изменения
6. Создайте Pull Request

### Запуск тестов
```bash
# Войти в Symfony контейнер
./docker-commands.sh shell

# Запустить тесты
php bin/phpunit
```

## 🤝 Поддержка

Если у вас возникли проблемы:

1. Проверьте логи: `./docker-commands.sh logs`
2. Убедитесь, что все контейнеры запущены: `docker-compose ps`
3. Проверьте статус миграций: `docker-compose exec symfony php bin/console doctrine:migrations:status`
4. Очистите кэш: `./docker-commands.sh cache-clear`

## 📄 Лицензия

Этот проект создан в рамках тестового задания для Team IT. 