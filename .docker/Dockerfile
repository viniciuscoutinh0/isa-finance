FROM alpine:3.22 AS builder

RUN apk add --no-cache \
    curl \
    gnupg \
    unixodbc-dev \
    autoconf \
    make \
    g++ \
    ca-certificates \
    php84 \
    php84-dev \
    php84-pear \
    php84-openssl

WORKDIR /tmp

COPY ./php/drivers/sqlsrv-5.13.3.tgz /tmp/sqlsrv-5.13.3.tgz
COPY ./php/drivers/pdo_sqlsrv-5.13.3.tgz /tmp/pdo_sqlsrv-5.13.3.tgz

RUN curl -O -k https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.10.6.1-1_amd64.apk \
    && apk add --allow-untrusted msodbcsql17_17.10.6.1-1_amd64.apk

RUN mkdir -p /tmp/sqlsrv /tmp/pdo_sqlsrv \
    && tar -xzf sqlsrv-5.13.3.tgz -C /tmp/sqlsrv --strip-components=1 \
    && tar -xzf pdo_sqlsrv-5.13.3.tgz -C /tmp/pdo_sqlsrv --strip-components=1 \
    && cd /tmp/sqlsrv && phpize84 && ./configure --with-php-config=php-config84 && make && make install \
    && cd /tmp/pdo_sqlsrv && phpize84 && ./configure --with-php-config=php-config84 && make && make install

FROM alpine:3.22

ARG USER=developer
ARG UID=1000

RUN apk update && apk add --no-cache \
    curl \
    ca-certificates \
    zip \
    unzip \
    sqlite \
    nodejs \
    npm \
    php84 \
    php84-fpm \
    php84-curl \
    php84-mbstring \
    php84-bcmath \
    php84-openssl \
    php84-pdo \
    php84-pdo_sqlite \
    php84-tokenizer \
    php84-fileinfo \
    php84-zip \
    php84-intl \
    php84-xml \
    php84-ctype \
    php84-phar \
    php84-pcntl \
    php84-session \
    php84-dom \
    php84-xmlreader \
    php84-xmlwriter \
    php84-simplexml \
    php84-iconv \
    php84-gd \
    php84-exif \
    php84-redis \
    php84-opcache \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/share/doc

RUN ln -s /usr/bin/php84 /usr/local/bin/php

RUN curl -O -k https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.10.6.1-1_amd64.apk \
    && apk add --allow-untrusted msodbcsql17_17.10.6.1-1_amd64.apk

COPY --from=builder /usr/lib/php84/modules/sqlsrv.so /usr/lib/php84/modules/
COPY --from=builder /usr/lib/php84/modules/pdo_sqlsrv.so /usr/lib/php84/modules/

RUN echo "extension=sqlsrv.so" > /etc/php84/conf.d/20_sqlsrv.ini \
    && echo "extension=pdo_sqlsrv.so" > /etc/php84/conf.d/10_pdo_sqlsrv.ini

RUN curl -sS https://getcomposer.org/installer | php \
    -- --install-dir=/usr/local/bin --filename=composer

RUN addgroup -g ${UID} -S ${USER} \
    && adduser -u ${UID} -S ${USER} -G ${USER} \
    && mkdir -p /var/log/ /var/run/ /var/www/html \
    && chown -R ${USER}:${USER} /var/log/ /var/run/ /var/www \
    && chmod -R 775 /var/log/ /var/run /var/www

COPY ./php/php-fpm.conf /etc/php84/php-fpm.conf
COPY ./php/php.ini /etc/php84/php.ini

WORKDIR /var/www/html

USER ${USER}

CMD ["php-fpm84", "--nodaemonize", "--fpm-config", "/etc/php84/php-fpm.conf"]
