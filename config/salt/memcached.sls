memcached:
  pkg:
    - installed
  service.running:
    - require:
      - pkg: memcached