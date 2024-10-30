# Note. If you change this, you also need to update docker-compose.yml.
# only useful in a setting with multiple services/ makefiles.
SERVICE_TARGET := wordpress
CLI_SERVICE := cli
COMPOSE_FILE := docker/docker-compose.yml
PROJECT_NAME := checkoutx_plugin

.DEFAULT_GOAL := start

# all our targets are phony (no files to check).
.PHONY: start up configure stop down reset-wordpress build rebuild logs clean shell prune release

start: up setup

start_fresh: up reset-wordpress

release: 
	rm -rf release/ && rsync -av --exclude-from='.releaseignore' ./ ./release/

stop:
	docker-compose --file $(COMPOSE_FILE) -p $(PROJECT_NAME) stop

down:
	docker-compose --file $(COMPOSE_FILE) -p $(PROJECT_NAME) down

build:
	# only build the container. Note, docker does this also if you apply other targets.
	docker-compose --file $(COMPOSE_FILE) build $(SERVICE_TARGET)

rebuild:
	docker-compose --file $(COMPOSE_FILE) build --no-cache $(SERVICE_TARGET)

logs:
	docker-compose --file $(COMPOSE_FILE) -p $(PROJECT_NAME) logs -f

clean: down
	# remove created images
	docker-compose --file $(COMPOSE_FILE) -p $(PROJECT_NAME) rm -v
	docker volume rm $(PROJECT_NAME)_wordpress $(PROJECT_NAME)_data $(PROJECT_NAME)_uploads_data

shell:
	docker-compose --file $(COMPOSE_FILE) -p $(PROJECT_NAME) exec $(SERVICE_TARGET) bash

prune:
	# clean all that is not actively used
	docker system prune -af

# "private" commands, used by start and start_fresh but you can run them
# independently as well
up:
	docker-compose --file ${COMPOSE_FILE} -p $(PROJECT_NAME) up --build -d

setup:
	docker-compose --file $(COMPOSE_FILE) -p $(PROJECT_NAME) run $(CLI_SERVICE) /setup/wait-for.sh db:3306 -- /setup/wp-heroku-release.sh
