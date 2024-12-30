FROM docker.vpn/bestnetwork/docker-php:8.3-dev as development

USER root

# Install NodeJS and node modules
RUN apk --update add nodejs yarn

# Start
USER www-data
WORKDIR /srv
CMD php -S 0.0.0.0:8000 -t public
EXPOSE 8000



FROM development as production

# Install dependencies
COPY --chown=www-data:www-data symfony/composer.json symfony/composer.lock symfony/symfony.lock /srv/
RUN composer install --prefer-dist --no-scripts --dev --no-autoloader && \
    rm -r ${HOME}/.ssh ${HOME}/.composer

# Import codebase
COPY --chown=www-data:www-data symfony /srv

# Finish
RUN composer dump-autoload --no-scripts --optimize --dev

# Warmup cache
ARG APP_VERSION=dev
ENV APP_VERSION=${APP_VERSION}
RUN console list

HEALTHCHECK --interval=10s --timeout=1s --retries=3 \
  CMD nc -z localhost 8000
