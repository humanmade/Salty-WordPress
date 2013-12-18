base:
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
    - local
    - node
