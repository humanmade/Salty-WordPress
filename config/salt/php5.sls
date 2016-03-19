# PHP5 modules and configuration
php5_pkgrepo:
  pkgrepo.managed:
    - ppa: ondrej/php

php_stack:
  pkg.installed:
    - name: php5.6-fpm
    - name: php5.6-gd
    - name: php5.6-mysql
    - name: php5.6-json
    - name: php5.6-memcache
    - name: php5.6-mcrypt
    - name: php5.6-curl
    - name: php5.6-imagick
    - name: php5.6-cli
    - name: php-apc
    - name: mysql-client
  service.running:
    - name: php5.6-fpm
    - watch:
      - file: /etc/php/5.6/fpm/php.ini
      - file: /etc/php/5.6/fpm/pool.d/www.conf

# php5-imagick also requires imagemagick
imagemagick:
  pkg.installed

libssh2-1-dev:
  pkg.installed:
    - name: libssh2-1-dev

libssh2-php:
  pkg.installed:
    - name: libssh2-php

composer:
  cmd.run:
    - name: curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer
    - unless: which composer

# Configuration files for php5-fpm

/etc/php/5.6/fpm/php.ini:
  file.managed:
    - source: salt://config/php5.6-fpm/php.ini
    - user: root
    - group: root
    - template: jinja
    - mode: 644

/etc/php/5.6/fpm/pool.d/www.conf:
  file.managed:
    - source: salt://config/php5.6-fpm/www.conf
    - user: root
    - group: root
    - template: jinja
    - mode: 644

{% if grains['user'] != 'vagrant' %}
/var/log/php.log:
  file.managed:
    - user: www-data
    - group: www-data
    - mode: 644
{% endif %}
