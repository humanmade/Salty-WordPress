# Salty WordPress

A flavorful way to manage your entire WordPress stack.

### Getting Building

Salty WordPress uses three great technologies: [Salt](http://saltstack.com/), [Vagrant](http://www.vagrantup.com/), and [WordPress](http://wordpress.org/). It's intended to get you building amazing projects as quickly and effectively as possible.

Here's how to get building:

1. Clone the repo: `cd ~/; git clone git@github.com:humanmade/Salty-WordPress.git`.
1. Install the latest versions of [Vagrant](http://downloads.vagrantup.com/) and [Virtual Box](https://www.virtualbox.org/wiki/Downloads).
1. Once Vagrant is installed, install [Salty Vagrant](https://github.com/saltstack/salty-vagrant) plugin to provision (aka configure) your virtual machine with Salt. All you need to do is `vagrant plugin install vagrant-salt` from your local machine. Ignore the other instructions on the Salty Vagrant repo. Salty Vagrant [will eventually be merged](https://github.com/mitchellh/vagrant/pull/1626) into Vagrant itself.
1. Change into the Salty WordPress directory and run `vagrant up`. This will take some time. Behind the scenes, Vagrant and Salt are downloading all of the system utilities (e.g. Nginx, PHP5-FPM, Memcached, etc.) to run your virtual machine.
1. In your `/etc/hosts` file, point any domains you plan to work on to `192.168.50.10`. The virtual machine is configured to handle all requests to `*.dev`. The WordPress trunk install, for instance, should be `wordpress-trunk.dev`.
1. Access your virtual machine with `vagrant ssh`.