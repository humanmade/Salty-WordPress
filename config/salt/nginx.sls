# Make sure nginx is installed and up
nginx:
  pkg:
    - installed
  service.running:
    - require:
      - pkg: nginx
    - watch:
      - file: /etc/nginx/nginx.conf
      - file: /etc/nginx/sites-enabled/default

# Configuration files for nginx
/etc/nginx/nginx.conf:
  file.managed:
    - source: salt://config/nginx/nginx.conf
    - user: root
    - group: root
    - mode: 644

/etc/nginx/fastcgi_params:
  file.managed:
    - source: salt://config/nginx/fastcgi_params
    - user: root
    - group: root
    - mode: 644

/etc/nginx/sites-enabled/default:
  file.managed:
    - source: salt://config/nginx/default
    - user: root
    - group: root
    - mode: 644

/srv/www:
  file.directory:
    - user: {{ grains['user'] }}
    - group: {{ grains['user'] }}
    - mode: 755
    - makedirs: True

/srv/www/default/index.php:
  file.managed:
    - user: {{ grains['user'] }}
    - group: {{ grains['user'] }}
    - mode: 644
    - contents: "not found"