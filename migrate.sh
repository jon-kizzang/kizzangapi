#!/bin/bash
#
if [ "$1" != "" ]; then
	if [ "$1" == "test" ]; then
		sudo /usr/bin/mysql -u root -pfreesia@#$ --execute='CREATE DATABASE IF NOT EXISTS test_freesia'
	fi

	cd www
	composer update

	cd application/db

	if [ "$1" == 'test' ]; then
		sed -i 's/ENV_DEV/ENV_TEST/g' ruckusing.conf.php
		sed -i 's/$active_group = "default"/$active_group = "test"/g' ../config/database.php
	else
		sed -i 's/ENV_TEST/ENV_DEV/g' ruckusing.conf.php
		sed -i 's/$active_group = "test"/$active_group = "default"/g' ../config/database.php
	fi

	# run ruckus migration
	php ../../vendor/ruckusing/ruckusing-migrations/ruckus.php db:migrate
else
	echo "[ERROR] You are missing the parameter."
	echo "1. For migration in development please run 'sudo ./migrate.sh dev'."
	echo "2. For migration in testing please run 'sudo ./migrate.sh test'"
fi

