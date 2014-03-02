#!/bin/sh -
#======================================================================================================================
# vim: softtabstop=4 shiftwidth=4 expandtab fenc=utf-8 spell spelllang=en cc=120
#======================================================================================================================
#
#          FILE: bootstrap-ubuntu.sh
#
#   DESCRIPTION: Bootstrap a WordPress server using Salty WordPress and Ubuntu.
#                Code cribbed from Salt Stack bootstrap
#
#          BUGS: https://github.com/humanmade/Salty-WordPress/issues
#
#======================================================================================================================

set -ex

#---  FUNCTION  -------------------------------------------------------------------------------------------------------
#          NAME:  __apt_get_install_noinput
#   DESCRIPTION:  (DRY) apt-get install with noinput options
#----------------------------------------------------------------------------------------------------------------------
__apt_get_install_noinput() {
    apt-get install -y -o DPkg::Options::=--force-confold $@; return $?
}

#---  FUNCTION  -------------------------------------------------------------------------------------------------------
#          NAME:  install_dependencies
#   DESCRIPTION:  (DRY) Install necessary dependencies for bootstrap
#----------------------------------------------------------------------------------------------------------------------
install_dependencies() {
    __apt_get_install_noinput git
    __apt_get_install_noinput salt-common salt-master salt-minion
}

#---  FUNCTION  -------------------------------------------------------------------------------------------------------
#          NAME:  init_environment
#   DESCRIPTION:  (DRY) Initialize requirements for the environment
#----------------------------------------------------------------------------------------------------------------------
init_environment() {
    # Create the ubuntu user
    if ! id -u ubuntu > /dev/null 2>&1; then
        useradd ubuntu -G sudo -d /home/ubuntu -m
    fi
}

#---  FUNCTION  -------------------------------------------------------------------------------------------------------
#          NAME:  init_salty_wordpress
#   DESCRIPTION:  (DRY) Initialize Salty WordPress
#----------------------------------------------------------------------------------------------------------------------
init_salty_wordpress() {
    cd /home/ubuntu/
    if [ ! -d /home/ubuntu/Salty-WordPress ]; then
        # @todo remove branch before merge
        sudo -u ubuntu -H git clone -b masterless https://github.com/humanmade/Salty-WordPress.git /home/ubuntu/Salty-WordPress
    fi
    # Put the config files in the right place
    rm -f /srv/salt
    ln -s /home/ubuntu/Salty-WordPress/config/salt /srv/salt
    # Put the minion file in the right place
    rm -f /etc/salt/minion
    ln -s /home/ubuntu/Salty-WordPress/config/salt/minions/masterless.conf /etc/salt/minion
}

#---  FUNCTION  -------------------------------------------------------------------------------------------------------
#          NAME:  provision_server
#   DESCRIPTION:  (DRY) Provision the server
#----------------------------------------------------------------------------------------------------------------------
provision_server() {
    salt-call --local state.highstate
}

# Let's go!
install_dependencies
init_environment
init_salty_wordpress
provision_server

echo "Initial provision is complete. Please remember to change your root, ubuntu and MySQL root passwords."

