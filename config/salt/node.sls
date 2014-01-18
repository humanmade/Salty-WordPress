npm-repo:
  pkgrepo.managed:
    - ppa: chris-lea/node.js
    - require_in:
      - pkg: nodejs

nodejs:
  pkg.installed

npm:
  pkg.installed

sass:
  gem.installed:
    - name: sass

grunt-cli:
  npm.installed:
    - name: grunt-cli
    - require:
      - pkg: nodejs
      - pkg: npm

yo:
  npm.installed

generator-hmbase:
  npm.installed

/home/{{ grains['user'] }}/.config/configstore/update-notifier-yo.yml:
  file.managed:
    - user: {{ grains['user'] }}
    - group: {{ grains['user'] }}  