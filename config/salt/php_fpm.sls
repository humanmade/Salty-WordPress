# PHP5 modules and configuration
php_stack:
  pkg.installed:
    - name: php5-fpm
  service.running:
    - name: php5-fpm
    - require:
      - pkg: php5-fpm
      - pkg: php5-gd
      - pkg: php5-mysql
      - pkg: php5-json
      - pkg: php5-memcache
      - pkg: php5-mcrypt
      - pkg: php5-curl
      - pkg: php5-cli
      - pkg: php-apc
      - pkg: mysql-client
    - watch:
      - file: /etc/php5/fpm/php.ini
      - file: /etc/php5/fpm/pool.d/www.conf

php_gd:
  pkg.installed:
    - name: php5-gd

php_mysql:
  pkg.installed:
    - name: php5-mysql

php_json:
  pkg.installed:
    - name: php5-json

php_memcache:
  pkg.installed:
    - name: php5-memcache

php_mcrypt:
  pkg.installed:
    - name: php5-mcrypt

php_curl:
  pkg.installed:
    - name: php5-curl

php_imagick:
  pkg.installed:
    - name: php5-imagick

# php5-imagick also requires imagemagick
imagemagick:
  pkg.installed

php_cli:
  pkg.installed:
    - name: php5-cli

php_apc:
  pkg.installed:
    - name: php-apc

mysql_client:
  pkg.installed:
    - name: mysql-client

composer:
  cmd.run:
    - name: curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer
    - unless: which composer
    - require:
      - pkg: php_cli

# Configuration files for php5-fpm

/etc/php5/fpm/php.ini:
  file.managed:
    - source: salt://config/php5-fpm/php.ini
    - user: root
    - group: root
    - template: jinja
    - mode: 644

/etc/php5/fpm/pool.d/www.conf:
  file.managed:
    - source: salt://config/php5-fpm/www.conf
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