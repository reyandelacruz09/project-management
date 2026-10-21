.PHONY: help setup dev dev-backoffice dev-all test test-filter format lint migrate db-seed db-fresh cache-clear cache-all docker-up docker-down docker-logs docker-build deploy build-backoffice

PHP ?= php
COMPOSER ?= composer
NPM ?= npm
BACKOFFICE_DIR ?= ../project-management-backoffice

# Default target
help:
	@echo "================================================================="
	@echo "                     PROJECT DESK MAKEFILE                       "
	@echo "================================================================="
	@echo "Local Development:"
	@echo "  make setup          - Complete local setup (env, deps, db, seeds)"
	@echo "  make dev            - Run Laravel dev server + Vite + Queue"
	@echo "  make dev-backoffice - Run Vue 3 backoffice dev server"
	@echo "  make dev-all        - Run Laravel backend and Vue backoffice together"
	@echo ""
	@echo "Testing & Code Quality:"
	@echo "  make test           - Run full PHPUnit test suite"
	@echo "  make format         - Format PHP code with Laravel Pint"
	@echo ""
	@echo "Database:"
	@echo "  make migrate        - Run database migrations"
	@echo "  make db-seed        - Seed the database"
	@echo "  make db-fresh       - Refresh database and run seeders"
	@echo ""
	@echo "Docker:"
	@echo "  make docker-up      - Start Docker Compose containers in background"
	@echo "  make docker-down    - Stop Docker Compose containers"
	@echo "  make docker-logs    - Follow Docker application logs"
	@echo "  make docker-build   - Build Docker container images"
	@echo ""
	@echo "Production & Deployment:"
	@echo "  make deploy         - Run production deployment routine (cache, migrate, optimize)"
	@echo "  make cache-all      - Cache config, routes, views, events"
	@echo "  make cache-clear    - Clear all application caches"
	@echo "================================================================="

# --- Local Setup ---
setup:
	@echo "--> Setting up local environment..."
	@test -f .env || cp .env.example .env
	$(COMPOSER) install
	$(NPM) install
	$(PHP) artisan key:generate --ansi
	@mkdir -p database
	@touch database/database.sqlite
	$(PHP) artisan migrate:fresh --seed --ansi
	@if [ -d "$(BACKOFFICE_DIR)" ]; then \
		echo "--> Setting up backoffice frontend..."; \
		test -f "$(BACKOFFICE_DIR)/.env" || cp "$(BACKOFFICE_DIR)/.env.example" "$(BACKOFFICE_DIR)/.env"; \
		(cd $(BACKOFFICE_DIR) && $(NPM) install); \
	fi
	@echo "--> Setup complete! Run 'make dev-all' to start both applications."

# --- Development Servers ---
dev:
	@echo "--> Starting Laravel development environment (server + vite + queue)..."
	$(COMPOSER) dev

dev-backoffice:
	@echo "--> Starting Vue backoffice development server..."
	@cd $(BACKOFFICE_DIR) && $(NPM) run dev

dev-all:
	@echo "--> Starting Laravel backend (http://127.0.0.1:8000) and Vue backoffice (http://localhost:5174)..."
	@trap 'kill 0' EXIT; \
	($(COMPOSER) dev) & \
	(cd $(BACKOFFICE_DIR) && $(NPM) run dev) & \
	wait

build-backoffice:
	@echo "--> Building Vue backoffice for production..."
	@cd $(BACKOFFICE_DIR) && $(NPM) run build

# --- Testing & Quality ---
test:
	@echo "--> Running PHPUnit test suite..."
	$(PHP) artisan test

format:
	@echo "--> Running Laravel Pint..."
	./vendor/bin/pint --dirty --format agent

lint: format

# --- Database ---
migrate:
	$(PHP) artisan migrate --ansi

db-seed:
	$(PHP) artisan db:seed --ansi

db-fresh:
	$(PHP) artisan migrate:fresh --seed --ansi

# --- Docker ---
docker-up:
	docker compose up -d

docker-down:
	docker compose down

docker-logs:
	docker compose logs -f app

docker-build:
	docker compose build

# --- Production Deployment ---
cache-all:
	@echo "--> Optimizing application for production..."
	$(PHP) artisan config:cache
	$(PHP) artisan route:cache
	$(PHP) artisan view:cache
	$(PHP) artisan event:cache

cache-clear:
	@echo "--> Clearing all caches..."
	$(PHP) artisan optimize:clear

deploy:
	@echo "--> Starting production deployment routine..."
	$(COMPOSER) install --no-dev --no-interaction --prefer-dist --optimize-autoloader
	$(NPM) ci
	$(NPM) run build
	$(PHP) artisan migrate --force --no-interaction
	$(MAKE) cache-all
	$(PHP) artisan queue:restart
	@if [ -d "$(BACKOFFICE_DIR)" ]; then \
		echo "--> Building production backoffice..."; \
		(cd $(BACKOFFICE_DIR) && $(NPM) ci && $(NPM) run build); \
	fi
	@echo "--> Deployment finished successfully!"
