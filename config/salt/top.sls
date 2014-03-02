base:

{% set states = salt['cp.list_states'](env) %}

  '*':
    - tools
    - config
    - nginx
    - php_fpm
    - postfix

  'salty-wordpress':
    - vagrant
    - tools.ruby
    - tools.python
    - memcached
    - mysql
    - node

  'role:masterless':
    - match: grain
    - memcached
    - mysql
    - tools.ruby

{% if 'local' in states %}
    - local
{% endif %}