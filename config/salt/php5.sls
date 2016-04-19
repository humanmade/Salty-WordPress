# PHP5 modules and configuration
php5:
  pkg.removed

php5.6_packages:
  pkg.installed:
    - pkgs:
      - php5.6-fpm
      - php5.6-gd
      - php5.6-mysql
      - php5.6-json
      - php5.6-mcrypt
      - php5.6-curl
      - php5.6-cli
      - php5.6-xml
      - php5.6-dev
      - php5.6-mbstring
      - php-apc
      - php-pear
      - imagemagick
      - libmagickwand-dev
      - libssh2-1-dev
      - libssh2-php

php5.6-pecl-config:
  cmd.run:
    - names:
      - pecl config-create / /etc/php/5.6/pecl.conf
      - pecl -C /etc/php/5.6/pecl.conf config-set php_suffix 5.6
      - pecl -C /etc/php/5.6/pecl.conf config-set php_bin /usr/bin/php5.6
    - unless: ls /etc/php/5.6/pecl.conf

php5.6-imagick:
  cmd.run:
    - names:
      - yes '' | pecl -C /etc/php/5.6/pecl.conf install imagick
      - echo "extension=imagick.so" > /etc/php/5.6/mods-available/imagick.ini
      - ln -s /etc/php/5.6/mods-available/imagick.ini /etc/php/5.6/cli/conf.d/imagick.ini
      - ln -s /etc/php/5.6/mods-available/imagick.ini /etc/php/5.6/fpm/conf.d/imagick.ini
      - sudo service php5.6-fpm restart
    - unless: php5.6 -m | grep Imagick

php5.6-memcache:
  cmd.run:
    - names:
      - yes '' | pecl -C /etc/php/5.6/pecl.conf install memcache
      - echo "extension=memcache.so" > /etc/php/5.6/mods-available/memcache.ini
      - ln -s /etc/php/5.6/mods-available/memcache.ini /etc/php/5.6/cli/conf.d/memcache.ini
      - ln -s /etc/php/5.6/mods-available/memcache.ini /etc/php/5.6/fpm/conf.d/memcache.ini
      - sudo service php5.6-fpm restart
    - unless: php5.6 -m | grep memcache

php5_stack:
  service.running:
    - name: php5.6-fpm
    - watch:
      - file: /etc/php/5.6/fpm/php.ini
      - file: /etc/php/5.6/fpm/pool.d/www.conf

composer:
  cmd.run:
    - names:
      - curl -sS https://getcomposer.org/installer | php
      - mv composer.phar /usr/local/bin/composer
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
