#!/bin/sh

echo "============================================"
echo "FR-WEBAPP - Database Provisioning"
echo "============================================"

mysql -u homestead -e "DROP DATABASE frmanager;"
mysql -u homestead -e "CREATE DATABASE frmanager;"
mysql -u homestead frmanager < projects/fr-webapp/Homestead/frmanager.sql

echo "------------------------------------------"
echo "FR-WEBAPP - Database Configured."
echo "------------------------------------------"