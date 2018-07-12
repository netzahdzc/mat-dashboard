# Parent images
FROM python:2.7-slim
FROM nginx:latest

# Working directory to:
COPY ./default.conf /etc/nginx/conf.d/default.conf
COPY ./ /usr/share/nginx/html

# Needed packages
RUN apt-get update && apt-get dist-upgrade
RUN apt-get install -y mysql-server