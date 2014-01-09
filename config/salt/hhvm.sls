hhvm-ppa:
  pkgrepo.managed:
    - humanname: HHVM PPA
    - name: deb http://dl.hhvm.com/ubuntu/ saucy main
    - require_in:
      - git

    - require_in:
      - hhvm
      - hhvm-fastcgi

hhvm:
  pkg.installed:
    - skip_verify: True

hhvm-fastcgi:
  pk.installed:
    - skip_verify: True