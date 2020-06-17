#!/bin/bash

docker build -t myphp:7.2.2-apache-rewrite -f Dockerfile .
docker-compose up -d