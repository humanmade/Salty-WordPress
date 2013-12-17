postfix:
  pkg:
    - installed
  service.running:
    - enable: True
    - require:
      - pkg: postfix
    - watch:
      - file: /etc/postfix/main.cf

# postfix main configuration file
/etc/postfix/main.cf:
  file.managed:
    - source: salt://config/postfix/main.cf
    - user: root
    - group: root
    - mode: 644
    - template: jinja
    - require:
      - pkg: postfix