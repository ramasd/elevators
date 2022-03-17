FROM nginx:stable-alpine

WORKDIR /etc/nginx/conf.d

COPY /nginx/default.conf .

WORKDIR /var/www/html

COPY /src .