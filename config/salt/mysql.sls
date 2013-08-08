mysql-server:
  pkg.installed:
    - name: mysql-server-5.5

mysql:
  service.running:
    - name: mysql
    - require:
      - pkg: mysql-server
    - watch:
      - file: /etc/mysql/my.cnf

/etc/mysql/my.cnf:
  file.managed:
    - source: salt://config/mysql/my.cnf
    - user: root
    - group: root
    - mode: 644