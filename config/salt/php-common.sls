php_pkgrepo:
  pkgrepo.managed:
    - name: deb http://ppa.launchpad.net/ondrej/php/ubuntu trusty main
    - keyid: 4F4EA0AAE5267A6C
    - keyserver: keyserver.ubuntu.com
    - require_in:
      - pkg: php5_stack
      - pkg: php7_stack
