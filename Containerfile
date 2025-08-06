FROM docker.io/library/php:8.4-cli AS base

ARG UID=1000
ARG GID=$UID
ARG TZ=UTC
ARG NODE_VERSION=22
ARG POSTGRES_VERSION=17

WORKDIR /app

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=${TZ}
ENV OCTANE_COMMAND="php -d variables_order=EGPCS /app/artisan octane:start --server=swoole --host=0.0.0.0 --port=8080"
ENV OCTANE_USER="docker"

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone

RUN cd /usr/local/src \
    && apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg curl ca-certificates zip unzip tar xz-utils git postgresql-common dnsutils fswatch \
    && curl -L https://github.com/BtbN/FFmpeg-Builds/releases/download/latest/ffmpeg-n7.1-latest-linux64-gpl-7.1.tar.xz | tar -xJ --strip-components=2 --exclude="doc" --exclude="man" -C /usr/local/bin \
    && apt-get install -y gifsicle jpegoptim optipng pngquant webp \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_VERSION}.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y \
    && apt-get install -y postgresql-client-${POSTGRES_VERSION} \
    && apt-get install -y nodejs \
    && curl -sLS https://dl.min.io/client/mc/release/linux-amd64/mc --create-dirs -o /usr/local/bin/mc \
    && chmod +x /usr/local/bin/mc \
    && npm install -g npm \
    && npm install -g pnpm \
    && npm install -g svgo

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN cp /usr/local/etc/php/php.ini-production ${PHP_INI_DIR}/php.ini \
    && install-php-extensions @composer apcu bcmath bz2 calendar ev event exif \
    pdo_mysql pdo_pgsql pgsql gettext imap inotify intl msgpack opcache pcntl pcov \
    redis sockets swoole uv zip gd igbinary imagick

COPY containers/runtimes/container-entrypoint.sh /usr/local/bin/container-entrypoint.sh
COPY containers/runtimes/php-production.ini ${PHP_INI_DIR}/conf.d/99-user.ini

RUN chmod +x /usr/local/bin/container-entrypoint.sh

RUN groupadd --force -g ${GID} docker \
    && useradd -ms /bin/bash --no-user-group -g ${GID} -u ${UID} docker

RUN apt-get -y autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/* /usr/local/src/* /tmp/* /var/tmp/*

EXPOSE 5173
EXPOSE 6001
EXPOSE 8080

ENTRYPOINT ["container-entrypoint.sh"]

FROM base as development

ENV OCTANE_COMMAND="${OCTANE_COMMAND} --watch"

COPY --from=base /usr/local/etc/php/php.ini-development ${PHP_INI_DIR}/php.ini
COPY containers/runtimes/php-development.ini ${PHP_INI_DIR}/conf.d/99-user.ini

FROM base as production

COPY . /app

RUN composer install --prefer-dist --no-interaction --optimize-autoloader

RUN php artisan storage:link
RUN pnpm install
