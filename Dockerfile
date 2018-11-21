FROM php:7.2

WORKDIR /opt/oauth2-quickbooks
ENV ENV_ROOT=/opt/oauth2-quickbooks
ENV OAUTH2_PROVIDER_CONFIG=${ENV_ROOT}/etc/oauth2

RUN apt-get update \
    && apt-get install -y \
        vim \
        git

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" 

ADD src src
ADD composer.json composer.json