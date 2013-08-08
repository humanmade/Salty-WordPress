wp-cli-tests-mysql:
  mysql_user.present:
    - name: wp_cli_test
    - password: password1
    - host: localhost
    - require:
      - pkg: python-mysqldb
  mysql_database.present:
    - name: wp_cli_test
    - require:
      - pkg: python-mysqldb
  mysql_grants.present:
    - grant: all privileges
    - database: wp_cli_test.*
    - user: wp_cli_test
    - require:
      - pkg: python-mysqldb

php_pear:
  pkg.installed:
    - name: php-pear

php_phpunit:
  cmd.run:
    - name: pear channel-discover pear.phpunit.de ; pear config-set auto_discover 1 ; sudo pear install phpunit/PHPUnit
    - unless: which phpunit
    - require:
      - pkg: php-pear

wp_cli_dev_build:
  cmd.run:
    - name: cd /srv/www/wp-cli; ./utils/dev-build
    - require:
      - service: php5-fpm
    - unless: ls /usr/bin/wp

/var/log/php.log:
  file.symlink:
    - target: /srv/logs/php.log