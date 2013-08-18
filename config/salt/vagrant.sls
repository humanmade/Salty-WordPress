wordpress-trunk:
  git.latest:
    - name: git://github.com/WordPress/WordPress.git
    - rev: master
    - target: /srv/www/wordpress-trunk.dev
    - runas: vagrant
    - submodules: True
    - force: False
    - require:
      - pkg: git
  mysql_database.present:
    - name: wordpress_trunk
    - require:
      - service: mysql
      - pkg: python-mysqldb
  cmd.run:
    - name: cd /srv/www/wordpress-trunk.dev; wp core config --dbname=wordpress_trunk --dbuser=root; wp core install --title="Salty WordPress" --url=http://wordpress-trunk.dev --admin_name=humanmade --admin_password=humanmade --admin_email=hello@hmn.md
    - unless: cd /srv/www/wordpress-trunk.dev; wp core is-installed
    - require:
      - cmd: wp_cli
      - service: mysql


wp-cli-tests-mysql:
  mysql_user.present:
    - name: wp_cli_test
    - password: password1
    - host: localhost
    - require:
      - service: mysql
      - pkg: python-mysqldb
  mysql_database.present:
    - name: wp_cli_test
    - require:
      - service: mysql
      - pkg: python-mysqldb
  mysql_grants.present:
    - grant: all privileges
    - database: wp_cli_test.*
    - user: wp_cli_test
    - require:
      - service: mysql
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

/var/log/php.log:
  file.symlink:
    - target: /srv/logs/php.log