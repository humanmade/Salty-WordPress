# Ruby is primarily used for hub and rmate

ruby:
  pkg.installed

ruby-hub:
  cmd.run:
    - name: curl http://defunkt.io/hub/standalone -sLo /usr/bin/hub && chmod +x /usr/bin/hub
    - unless: ls /usr/bin/hub
  require:
    - name: ruby

ruby-rmate:
  gem.installed:
    - name: rmate