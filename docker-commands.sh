#!/bin/bash

# Docker commands for Symfony + PostgreSQL + Nginx setup

case "$1" in
    "build")
        echo "Building Docker containers..."
        docker-compose build
        ;;
    "up")
        echo "Starting all services..."
        docker-compose up -d
        ;;
    "bash")
        echo "Opening bash in Symfony container..."
        docker-compose exec symfony bash
        ;;
    "down")
        echo "Stopping all services..."
        docker-compose down
        ;;
    "restart")
        echo "Restarting all services..."
        docker-compose restart
        ;;
    "logs")
        echo "Showing logs..."
        docker-compose logs -f
        ;;
    "symfony-logs")
        echo "Showing Symfony logs..."
        docker-compose logs -f symfony
        ;;
    "nginx-logs")
        echo "Showing Nginx logs..."
        docker-compose logs -f nginx
        ;;
    "db-logs")
        echo "Showing database logs..."
        docker-compose logs -f database
        ;;
    "shell")
        echo "Opening shell in Symfony container..."
        docker-compose exec symfony bash
        ;;
    "db-shell")
        echo "Opening shell in database container..."
        docker-compose exec database psql -U app -d app
        ;;
    "migrate")
        echo "Running database migrations..."
        docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
        ;;
    "cache-clear")
        echo "Clearing Symfony cache..."
        docker-compose exec symfony php bin/console cache:clear
        ;;
    "composer-install")
        echo "Installing Composer dependencies..."
        docker-compose exec symfony composer install
        ;;
    "composer-update")
        echo "Updating Composer dependencies..."
        docker-compose exec symfony composer update
        ;;
    *)
        echo "Usage: $0 {build|up|down|restart|logs|symfony-logs|nginx-logs|db-logs|shell|db-shell|migrate|cache-clear|composer-install|composer-update}"
        echo ""
        echo "Commands:"
        echo "  build           - Build Docker containers"
        echo "  up              - Start all services"
        echo "  down            - Stop all services"
        echo "  restart         - Restart all services"
        echo "  logs            - Show all logs"
        echo "  symfony-logs    - Show Symfony logs"
        echo "  nginx-logs      - Show Nginx logs"
        echo "  db-logs         - Show database logs"
        echo "  shell           - Open shell in Symfony container"
        echo "  db-shell        - Open shell in database container"
        echo "  migrate         - Run database migrations"
        echo "  cache-clear     - Clear Symfony cache"
        echo "  composer-install - Install Composer dependencies"
        echo "  composer-update - Update Composer dependencies"
        echo "  bash            - Open bash in Symfony container"
        exit 1
        ;;
esac 