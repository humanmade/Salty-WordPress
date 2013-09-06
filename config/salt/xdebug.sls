php5-xdebug:
  pkg:
    - installed

/etc/php5/cli/conf.d/20-xdebug.ini:
  file.managed:
    - source: salt://config/xdebug/20-xdebug.ini
    - user: root
    - group: root
    - mode: 644