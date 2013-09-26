start_conf:
  file.managed:
    - name: /etc/init/salty-wordpress.conf
    - source: salt://config/start_conf

zshrc:
  file.managed:
    - name: /home/{{ grains['user'] }}/.zshrc
    - source: salt://config/zshrc

zsh_theme:
  file.managed:
    - name: /home/{{ grains['user'] }}/.oh-my-zsh/themes/joeyhoyle.zsh-theme
    - source: salt://config/zsh-themes/joeyhoyle.zsh-theme
    - require:
      - git.latest: oh_my_zsh

{{ grains['user'] }}:
  user.present:
    - shell: /bin/zsh

github.com:
  ssh_known_hosts:
    - present
    - user: {{ grains['user'] }}
    - fingerprint: 16:27:ac:a5:76:28:2d:36:63:1b:56:4d:eb:df:a6:48
