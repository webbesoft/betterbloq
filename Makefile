stripe-listen:
	stripe listen --forward-to localhost:8000/stripe/webhook --skip-verify

develop:
	docker compose up -d
	composer run dev
