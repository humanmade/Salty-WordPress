function get_host {
	
	if [ -f /etc/salt/grains ]
		then
			echo $( cat /etc/salt/grains | ack "project: (\S*)" --output='$1' )-$( echo `hostname` )''
	fi

	if [ ! -f /etc/salt/grains ]
		then 
			echo $( echo `hostname` )''
	fi
}

PROMPT='%{$fg_bold[red]%}$(get_host) ➜ %{$fg_bold[green]%}%p %{$fg[cyan]%}%c %{$fg_bold[blue]%}%{$fg_bold[blue]%} % %{$reset_color%}'

ZSH_THEME_GIT_PROMPT_PREFIX="git:(%{$fg[red]%}"
ZSH_THEME_GIT_PROMPT_SUFFIX="%{$reset_color%}"
ZSH_THEME_GIT_PROMPT_DIRTY="%{$fg[blue]%}) %{$fg[yellow]%}✗%{$reset_color%}"
ZSH_THEME_GIT_PROMPT_CLEAN="%{$fg[blue]%})"
