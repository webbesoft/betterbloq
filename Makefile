DOCKER_COMPOSE := docker compose
DOCKER_COMPOSE_PROD := $(DOCKER_COMPOSE) -f compose.prod.yaml
STRIPE_LISTEN := stripe listen --forward-to localhost:8000/stripe/webhook --skip-verify

.PHONY: stripe-listen develop build-staging build-prod

stripe-listen:
	$(STRIPE_LISTEN)

develop:
	$(DOCKER_COMPOSE) up -d
	composer install
	composer run dev

build-staging:
	git checkout staging || true
	docker compose -f compose.prod.yaml up --build -d

build-prod:
	git checkout main || true
	$(DOCKER_COMPOSE_PROD) build php-fpm-prod

# Default destination
DEST ?= staging

kamal-reboot-proxy:
	@if [ -f .env.$(DEST) ]; then \
		echo "Rebooting proxy for $(DEST) with environment from .env.$(DEST)"; \
		export $$(cat .env.$(DEST) | grep -v '^#' | xargs) && kamal proxy reboot -d $(DEST); \
	else \
		echo "Error: .env.$(DEST) file not found"; \
		exit 1; \
	fi

# Load environment variables from .env file based on destination
kamal-setup:
	@if [ -f .env.$(DEST) ]; then \
		echo "Loading environment from .env.$(DEST)"; \
		export $$(cat .env.$(DEST) | grep -v '^#' | xargs) && kamal setup -d $(DEST); \
	else \
		echo "Error: .env.$(DEST) file not found"; \
		exit 1; \
	fi

kamal-deploy:
	@if [ -f .env.$(DEST) ]; then \
		echo "Deploying to $(DEST) with environment from .env.$(DEST)"; \
		export $$(cat .env.$(DEST) | grep -v '^#' | xargs) && kamal deploy -d $(DEST); \
	else \
		echo "Error: .env.$(DEST) file not found"; \
		exit 1; \
	fi

kamal-app:
	@if [ -f .env.$(DEST) ]; then \
		echo "Deploying app to $(DEST) with environment from .env.$(DEST)"; \
		export $$(cat .env.$(DEST) | grep -v '^#' | xargs) && kamal app deploy -d $(DEST); \
	else \
		echo "Error: .env.$(DEST) file not found"; \
		exit 1; \
	fi

kamal-accessory:
	@if [ -f .env.$(DEST) ]; then \
		echo "Deploying accessories to $(DEST) with environment from .env.$(DEST)"; \
		export $$(cat .env.$(DEST) | grep -v '^#' | xargs) && kamal accessory boot all -d $(DEST); \
	else \
		echo "Error: .env.$(DEST) file not found"; \
		exit 1; \
	fi