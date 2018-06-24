FROM php:7.0-cli
MAINTAINER Lee <lonisy@163.com>

RUN apt-get update --assume-yes && apt-get install libicu-dev libpq-dev -y
RUN apt-get install -y zlib1g zlib1g-dev zip && /usr/local/bin/docker-php-ext-install zip

COPY php.ini /usr/local/etc/php/
RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai  /etc/localtime

COPY redis-3.1.3.tgz /home/redis.tgz
RUN pecl install /home/redis.tgz && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini

RUN pecl install swoole && echo "extension=swoole.so" > /usr/local/etc/php/conf.d/swoole.ini

WORKDIR /data
VOLUME ["/data"]
CMD ["/data/ImServer.sh"]

