FROM ghcr.io/sil-org/php8:8.3

LABEL maintainer="gtis_itse@groups.sil.org"

ARG GITHUB_REF_NAME
ENV GITHUB_REF_NAME=$GITHUB_REF_NAME

RUN <<EOT
    apt-get update -y
    apt-get --no-install-recommends install -y jq php-gmp ssl-cert
    apt-get clean
    rm -rf /var/lib/apt/lists/*
    a2enmod ssl
EOT

COPY dockerbuild/vhost.conf /etc/apache2/sites-enabled/

ARG UID=1001
ARG GID=1001

RUN <<EOT
    # ErrorLog inside a VirtualHost block is ineffective for unknown reasons
    sed -i -E 's@ErrorLog .*@ErrorLog /proc/1/fd/2@i' /etc/apache2/apache2.conf

    composer self-update --no-interaction

    groupadd -g "$GID" user
    useradd -m -u "$UID" -g "$GID" user
    mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2
    chown -R user:user /var/run/apache2 /var/lock/apache2 /var/log/apache2 /var/www/html
EOT

USER user
WORKDIR /data

COPY --chown=user dockerbuild/run.sh /data/
COPY --chown=user dockerbuild/run-idp.sh /data/
COPY --chown=user dockerbuild/run-spidplinks.php /data/
COPY --chown=user dockerbuild/apply-dictionaries-overrides.php /data/

# Note the name change: repos extending this one should only run the metadata
# tests, so those are the only tests we make available to them.
COPY --chown=user dockerbuild/run-metadata-tests.sh /data/run-tests.sh
COPY --chown=user tests/MetadataTest.php /data/tests/MetadataTest.php

# Install/cleanup composer dependencies
ARG COMPOSER_FLAGS="--prefer-dist --no-interaction --no-dev --optimize-autoloader --no-scripts --no-progress"
COPY --chown=user composer.json /data/
COPY --chown=user composer.lock /data/
RUN composer install $COMPOSER_FLAGS

ENV SSP_PATH=/data/vendor/simplesamlphp/simplesamlphp

# Copy modules into simplesamlphp
COPY --chown=user modules/ $SSP_PATH/modules

# Copy material theme templates to other modules, just in case the "default" theme is selected
COPY --chown=user modules/material/themes/material/default/* $SSP_PATH/modules/default/templates/
COPY --chown=user modules/material/themes/material/expirychecker/* $SSP_PATH/modules/expirychecker/templates/
COPY --chown=user modules/material/themes/material/mfa/* $SSP_PATH/modules/mfa/templates/
COPY --chown=user modules/material/themes/material/profilereview/* $SSP_PATH/modules/profilereview/templates/
COPY --chown=user modules/material/themes/material/silauth/* $SSP_PATH/modules/silauth/templates/

# Copy in SSP override files
COPY --chown=user dockerbuild/config/* $SSP_PATH/config/
COPY --chown=user dockerbuild/ssp-overrides/sp-php.patch sp-php.patch
RUN <<EOT
    patch /data/vendor/simplesamlphp/simplesamlphp/modules/saml/src/Auth/Source/SP.php sp-php.patch

    # create features directory so the Docker Compose volume has a place to land
    mkdir -p /data/features

    mkdir -p /data/cache
EOT

EXPOSE 80 443
CMD ["/data/run.sh"]
