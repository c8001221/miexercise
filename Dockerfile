FROM php:7.2.2-apache

ENV DISTANCE_API_KEY=[GOOGLE_API_KEY_HERE]

RUN docker-php-ext-install mysqli
RUN a2enmod rewrite