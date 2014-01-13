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
  pkg.installed

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
    - name: curl https://raw.github.com/wp-cli/wp-cli.github.com/master/installer.sh | bash
    - unless: which wp
    - user: {{ grains['user'] }}
    - require:
      - pkg: php5-cli
      - pkg: git
  file.symlink:
    - name: /usr/bin/wp
    - target: /home/{{ grains['user'] }}/.wp-cli/bin/wp

oh_my_zsh:
  git.latest:
    - name: git://github.com/robbyrussell/oh-my-zsh.git
    - rev: master
    - target: /home/{{ grains['user'] }}/.oh-my-zsh
    - runas: root
    - submodules: False
    - force: False
    - require:
      - pkg: zsh
      - pkg: git
