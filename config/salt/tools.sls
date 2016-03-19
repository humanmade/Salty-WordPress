# Editors

vim:
  pkg.installed:
    - name: vim

nano:
  pkg.installed:
    - name: nano


# Make the CLI experience more awesome
autojump:
  pkg.installed

# Ensure server time is always in-sync
ntp:
  pkg:
    - installed
  service:
    - running

# Uncategorized

curl:
  pkg.installed:
    - name: curl

git-ppa:
  pkgrepo.managed:
    - humanname: Git Core PPA
    - name: deb http://ppa.launchpad.net/git-core/ubuntu/ precise main
    - ppa: git-core/ppa
    - require_in:
      - git

git:
  pkg.installed

svn:
  pkg.installed:
    - name: subversion

tig:
  pkg.installed:
    - require:
      - pkg: git

zip:
  pkg:
    - installed

ack-grep:
  pkg:
    - installed

zsh:
  pkg:
    - installed

htop:
  pkg:
    - installed

wp_cli:
  cmd.run:
    - name: curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli-nightly.phar; chmod +x wp-cli-nightly.phar; sudo mv wp-cli-nightly.phar /usr/bin/wp
    - user: {{ grains['user'] }}

wp_cli_bash:
  file.managed:
    - name: /home/{{ grains['user'] }}/.wp_cli_completion.bash
    - source: salt://config/wp_cli_completion

oh_my_zsh:
  git.latest:
    - name: git://github.com/robbyrussell/oh-my-zsh.git
    - rev: master
    - target: /home/{{ grains['user'] }}/.oh-my-zsh
    - user: root
    - submodules: False
    - force: False
    - require:
      - pkg: zsh
      - pkg: git
