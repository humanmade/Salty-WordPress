base:

{% set states = salt['cp.list_states'](env) %}

  '*':
    - tools
    - config
    - nginx
    - php-common
    - php5
    - php7
    - postfix

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
