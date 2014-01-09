base:

{% set states = salt['cp.list_states'](env) %}

  '*':
    - tools
    - config
    - nginx
    - php_fpm
    - postfix
    - hhvm

  'salty-wordpress':
    - vagrant
    - tools.ruby
    - tools.python
    - memcached
    - mysql
    - node

{% if 'local' in states %}
    - local
{% endif %}
