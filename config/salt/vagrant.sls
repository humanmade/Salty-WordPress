wordpress-develop:
  git.latest:
    - name: git://develop.git.wordpress.org/
    - rev: master
    - target: /srv/www/wordpress-develop.dev
    - user: vagrant
    - submodules: True
    - force: False
    - require:
      - pkg: git
  mysql_database.present:
    - name: wordpress_develop
    - require:
      - service: mysql
      - pkg: python-mysqldb
  cmd.run:
    - name: cd /srv/www/wordpress-develop.dev; wp core config --dbname=wordpress_develop --dbuser=root; wp core install --title="WordPress.org" --url=http://wordpress-develop.dev --admin_name=wordpress --admin_password=wordpress --admin_email=wordpress@wordpress.org
    - unless: cd /srv/www/wordpress-develop.dev; wp core is-installed
    - user: {{ grains['user'] }}
    - require:
      - cmd: wp_cli
      - git: git://develop.git.wordpress.org/
      - mysql_database: wordpress_develop
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


php_phpunit:
  cmd.run:
    - name: wget https://phar.phpunit.de/phpunit-old.phar && chmod +x phpunit-old.phar && sudo mv phpunit-old.phar /usr/local/bin/phpunit
    - unless: which phpunit

{% if not salt['file.file_exists' ]('/var/log/php.log') %}
/var/log/php.log:
  file.symlink:
    - target: /srv/logs/php.log
{% endif %}

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
