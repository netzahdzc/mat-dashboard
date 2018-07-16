# Parent images
FROM python:2.7-slim
FROM nginx:latest

# Working directory to:
COPY ./default.conf /etc/nginx/conf.d/default.conf
COPY ./ /usr/share/nginx/html

# Needed packages
RUN apt-get update -y && apt-get dist-upgrade -y
RUN apt-get install -y mysql-server
