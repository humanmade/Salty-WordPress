# Salty WordPress

A flavorful way to manage your entire WordPress stack. Built and maintained by [Human Made](http://hmn.md/).

**Coming soon**: Provisioning production servers with Salty WordPress.

### Getting Building

Salty WordPress uses three great technologies: [Salt](http://saltstack.com/), [Vagrant](http://www.vagrantup.com/), and [WordPress](http://wordpress.org/). It's intended to get you building amazing projects as quickly and effectively as possible.

Here's how to get building:

1. Clone the repo: `cd ~/; git clone git@github.com:humanmade/Salty-WordPress.git`.
1. Install the latest version of [Vagrant](https://www.vagrantup.com/downloads.html) and version 4.2.12 of [Virtual Box](https://www.virtualbox.org/wiki/Download_Old_Builds_4_2).
1. Salty WordPress can also be used with VMWare 6.x instead of Virtualbox. There is a known issue where you'll need to install `nfs-common` in the VM before your shared directories will work. Keep in mind that, because the shared folders aren't mounted, your first provision will look like this: `vagrant up --provider=vmware_fusion; vagrant ssh; sudo apt-get install nfs-common; exit; vagrant halt; vagrant up --provision;` This will be fixed in Vagrant 1.5.
1. Change into the Salty WordPress directory and run `vagrant up`. This will take some time. Behind the scenes, Vagrant and Salt are downloading all of the system utilities (e.g. Nginx, PHP5-FPM, Memcached, etc.) to run your virtual machine.
1. In your `/etc/hosts` file, point any domains you plan to work on to `192.168.50.10`. The virtual machine is configured to handle all requests to `*.dev`. The WordPress develop install, for instance, should be `wordpress-develop.dev`.
1. Access your virtual machine with `vagrant ssh`. Windows users: You will see an output of the SSH info and the location of the key file instead. Feed this information into any SSH program, but not cmd.exe. Vagrant [suggests PuTTY](http://docs-v1.vagrantup.com/v1/docs/getting-started/ssh.html).

Navigate to `wordpress-develop.dev/src/` in your browser to see a fully-functional WordPress install, powered by Salty WordPress. The default admin username/password is `humanmade/humanmade`.

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

For instance, to more easily contribute to WP-CLI, use the following in your `Customfile` to have WP-CLI loaded from a shared directory:

`config.vm.synced_folder "wp-cli", "/home/vagrant/.wp-cli", :nfs => true`

If you'd like to persist your databases between each destroy, you might want to put them in a mapped directory:

`config.vm.synced_folder "databases", "/var/lib/mysql"`

Note: You'll need to do an initial provision, then copy all of the files in `/var/lib/mysql` to a "databases" directory in your local machine.

If you have the [Vagrant Hosts Updater](https://github.com/cogitatio/vagrant-hostsupdater) plugin installed you can add any additional hosts without having to edit `/etc/hosts`. You may have to reload the VM to see the changes.

```
if defined?(VagrantPlugins::HostsUpdater)
  config.hostsupdater.aliases = [ "wordpress-develop.dev", ... ]
end
```

## Contribution guidelines ##

see https://github.com/humanmade/Salty-WordPress/blob/master/CONTRIBUTING.md
