up:
	docker compose up -d --build
reseed:
	docker compose exec -T web php scripts/migrate.php && \
	docker compose exec -T web php scripts/seed.php
down:
	docker compose down -v
logs:
	docker compose logs -f web