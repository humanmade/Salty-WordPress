# Salty WordPress

A flavorful way to manage your entire WordPress stack. Built and maintained by [Human Made](http://hmn.md/).

**Coming soon**: Provisioning production servers with Salty WordPress.

### Getting Building

Salty WordPress uses three great technologies: [Salt](http://saltstack.com/), [Vagrant](http://www.vagrantup.com/), and [WordPress](http://wordpress.org/). It's intended to get you building amazing projects as quickly and effectively as possible.

Here's how to get building:

1. Clone the repo: `cd ~/; git clone git@github.com:humanmade/Salty-WordPress.git`.
1. Install the latest version of [Vagrant](http://downloads.vagrantup.com/) and version 4.2.12 of [Virtual Box](https://www.virtualbox.org/wiki/Download_Old_Builds_4_2).
1. Change into the Salty WordPress directory and run `vagrant up`. This will take some time. Behind the scenes, Vagrant and Salt are downloading all of the system utilities (e.g. Nginx, PHP5-FPM, Memcached, etc.) to run your virtual machine.
1. In your `/etc/hosts` file, point any domains you plan to work on to `192.168.50.10`. The virtual machine is configured to handle all requests to `*.dev`. The WordPress trunk install, for instance, should be `wordpress-trunk.dev`.
1. Access your virtual machine with `vagrant ssh`.

Navigate to `wordpress-trunk.dev` in your browser to see a fully-functional WordPress install, powered by Salty WordPress. The default admin username/password is `humanmade/humanmade`.

## Neat Tricks

Make your Salty WordPress experience even more awesome with these neat tricks.

### CLI Hacks

 - Deserialize data with `$~ serialize '[php serialized code]'`
 - Display a timestamp as a human readable string `$~ timestamp 1143123342` [ouputs "2006-03-23T14:15:42+00:00"]


### Open Remote Files in Sublime Text

Using couple of neat tools, [rsub](https://github.com/henrikpersson/rsub) and [rmate](https://github.com/textmate/rmate), you can open files located in Vagrant in Sublime Text. We've even bound `subl` so the syntax is similar to what you have in your local machine.

However, for this functionality to work properly, you'll need to SSH into Vagrant using SSH (and port forwarding), not `vagrant ssh`. Use `vagrant ssh-config` ([ref](http://docs.vagrantup.com/v2/cli/ssh_config.html)) to generate what you need to put in `~/.ssh/config`. Then, add `RemoteForward 52698 127.0.0.1:52698` to the entry ([ref](https://github.com/henrikpersson/rsub#ssh-tunneling)).

Now, when you SSH into Vagrant, you'll automatically set up a connection for rmate to communicate to rsub (a Sublime Text plugin).

### Localize Your Environment

Salty WordPress lets you localize your environment without having to edit tracked files. Create a local Salt file at `config/salt/local.sls` and Vagrant will include any declarations in the next provision.

Alternatively, customize your Vagrantfile by including a `Customfile` in Salty WordPress' base directory. You can include many declarations you might normally put in your Vagrantfile.
