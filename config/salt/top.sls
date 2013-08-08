base:
  '*':
    - tools
    - config
    - nginx
    - php_fpm

  'salty-wordpress':
    - vagrant
    - tools.ruby
    - memcached
    - mysql
