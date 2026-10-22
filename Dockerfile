FROM composer:2@sha256:da624c2c705d54e2f5a9bb77a5dc6c7d091711433ebefd7035672e84c2010fee
WORKDIR /app
COPY . .
RUN php -r 'if (version_compare(PHP_VERSION, "8.5.12", "<")) exit(1);'
USER 1000:1000
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
