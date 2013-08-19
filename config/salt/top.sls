base:
  '*':
    - tools
    - config
    - nginx
    - php_fpm

  'salty-wordpress':
    - vagrant
    - tools.ruby
    - tools.python
    - memcached
    - mysql
    - projects.*
