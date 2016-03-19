# PHP7 modules and configuration
php7_pkgrepo:
  pkgrepo.managed:
    - ppa: ondrej/php

php7_stack:
  pkg.installed:
    - name: php7.0-fpm
    - name: php7.0-gd
    - name: php7.0-mysql
    - name: php-memcached
    - name: php7.0-mcrypt
    - name: php7.0-curl
    - name: php7.0-cli
    - name: php7.0-opcache
    - name: mysql-client
    - name: php7.0-json
    - name: php-ssh2
    - name: php7.0-dev
    - name: pkg-config
  service.running:
    - name: php7.0-fpm
    - require:
      - file: /etc/php/7.0/fpm/conf.d/20-mcrypt.ini
      - file: /etc/php/7.0/fpm/php.ini
      - file: /etc/php/7.0/fpm/pool.d/www.conf
    - watch:
      - file: /etc/php/7.0/fpm/php.ini
      - file: /etc/php/7.0/fpm/pool.d/www.conf

/etc/php/7.0/fpm/conf.d/20-mcrypt.ini:
  file.symlink:
    - target: ../../mods-available/mcrypt.ini
    - require:
      - pkg: php7.0-mcrypt

/etc/php/7.0/cli/conf.d/20-mcrypt.ini:
  file.symlink:
    - target: ../../mods-available/mcrypt.ini
    - require:
      - pkg: php7.0-mcrypt

# php7.0-imagick also requires imagemagick
#
libmagickwand-dev:
  pkg.installed

/etc/php/7.0/fpm/php.ini:
  file.managed:
    - source: salt://config/php5-fpm/php.ini
    - user: root
    - group: root
    - mode: 644
    - template: jinja

/etc/php/7.0/fpm/pool.d/www.conf:
  file.managed:
    - source: salt://config/php5-fpm/www.conf
    - user: root
    - group: root
    - mode: 644

pecl:
  pkg.installed:
    - name: php-pear

php_imagemagick:
  pecl.installed:
    - name: imagick
    - defaults: True
