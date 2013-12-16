base:
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
    - local
    - node
