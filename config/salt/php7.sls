# PHP7 modules and configuration
php7_pkgrepo:
  pkgrepo.managed:
    - name: deb http://ppa.launchpad.net/ondrej/php/ubuntu trusty main

php7.0-fpm:
  pkg.installed

php7.0-gd:
  pkg.installed

php7.0-mysql:
  pkg.installed

php7.0-mcrypt:
  pkg.installed

php7.0-curl:
  pkg.installed

php7.0-cli:
  pkg.installed

php7.0-opcache:
  pkg.installed

php7.0-json:
  pkg.installed

php7.0-dev:
  pkg.installed

php-memcached:
  pkg.installed

php-ssh2:
  pkg.installed

pkg-config:
  pkg.installed

php7_stack:
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

/etc/php/7.0/cli/conf.d/20-mcrypt.ini:
  file.symlink:
    - target: ../../mods-available/mcrypt.ini

# php7.0-imagick also requires imagemagick
#
libmagickwand-dev:
  pkg.installed

/etc/php/7.0/fpm/php.ini:
  file.managed:
    - source: salt://config/php7.0-fpm/php.ini
    - user: root
    - group: root
    - mode: 644
    - template: jinja

/etc/php/7.0/fpm/pool.d/www.conf:
  file.managed:
    - source: salt://config/php7.0-fpm/www.conf
    - user: root
    - group: root
    - mode: 644
    - template: jinja

pecl:
  pkg.installed:
    - name: php-pear

php_imagemagick:
  pecl.installed:
    - name: imagick
    - defaults: True
