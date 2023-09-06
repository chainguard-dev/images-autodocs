FROM cgr.dev/chainguard/php:latest-dev AS builder
USER root
COPY . /app
RUN cd /app && chown -R php.php /app
USER php
RUN composer install --no-progress --no-dev --prefer-dist

FROM cgr.dev/chainguard/php:latest
COPY --from=builder /app /app

ENTRYPOINT [ "php", "/app/autodocs" ]

