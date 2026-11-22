


Unit tests
======

```command
```

Integration tests
=====

Run integration tests:
```command
export COMPOSE_FILE=$PWD/docker/docker-compose.test.yml
docker compose up -d atom atom_worker nginx elasticsearch percona memcached gearmand
docker compose ps
docker exec docker-atom-1 php -d memory_limit=-1 symfony tools:install   --database-host=percona --database-port=3306 --database-name=atom   --database-user=atom --database-password=atom_12345   --search-host=elasticsearch --search-port=9200 --search-index=atom   --demo --no-confirmation
docker exec docker-atom-1 php -d memory_limit=-1 symfony search:populate
docker compose -f docker/docker-compose.test.yml run --rm cypress
```
