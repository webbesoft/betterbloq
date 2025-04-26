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
