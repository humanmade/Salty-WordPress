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
    - user: {{ grains['user'] }}
    - require:
      - cmd: wp_cli
      - git: git://github.com/WordPress/WordPress.git
      - mysql_database: wordpress_trunk
      - service: mysql
      - pkg: php5-mysql


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


php_phpunit:
  cmd.run:
    - name: wget https://phar.phpunit.de/phpunit.phar && chmod +x phpunit.phar && sudo mv phpunit.phar /usr/local/bin/phpunit
    - unless: which phpunit


/var/log/php.log:
  file.symlink:
    - target: /srv/logs/php.log

dnsmasq:
  pkg:
    - installed
  service.running:
    - name: dnsmasq
    - watch:
      - file: /etc/dnsmasq.conf
  file.managed:
    - name: /etc/dnsmasq.conf
    - contents: address=/.dev/192.168.50.10
