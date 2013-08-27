# Ruby is primarily used for hub and rmate

ruby:
  pkg.installed

ruby-hub:
  cmd.run:
    - name: curl http://hub.github.com/standalone -sLo /usr/bin/hub && chmod +x /usr/bin/hub
    - unless: ls /usr/bin/hub
  require:
    - name: ruby

ruby-rmate:
  gem.installed:
    - name: rmate

ruby-compass:
  gem.installed:
    - name: compass