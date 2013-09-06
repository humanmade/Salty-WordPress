# -*- mode: ruby -*-
# vi: set ft=ruby :

# 'projects' and 'logs' directories are ignored in the repo, so let's make sure they exist
FileUtils.mkdir_p(File.dirname(__FILE__)+'/projects')
FileUtils.mkdir_p(File.dirname(__FILE__)+'/logs')
FileUtils.mkdir_p(File.dirname(__FILE__)+'/databases')

def Kernel.is_mac?
    # Detect if we are running on Mac
    RUBY_PLATFORM.scan(/-darwin/).length == 1
end

Vagrant.configure("2") do |config|

	config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--memory", 512]
  end

  config.vm.box = "raring-1304"
  config.vm.box_url = "http://cloud-images.ubuntu.com/vagrant/raring/current/raring-server-cloudimg-i386-vagrant-disk1.box"

  config.vm.hostname = "salty-wordpress"
  config.vm.network :private_network, ip: "192.168.50.10"

  config.ssh.forward_agent = true

  # fixes an issue with latest virtualbox
  # config.ssh.max_tries = 150

  nfs = Kernel.is_mac?
  config.vm.synced_folder "config", "/home/vagrant/config", :nfs => nfs
  config.vm.synced_folder "projects", "/srv/www", :nfs => nfs
  config.vm.synced_folder "databases", "/var/lib/mysql", :mount_options => ["dmode=777","fmode=777"]
  config.vm.synced_folder "logs", "/srv/logs", :nfs => nfs

  config.vm.synced_folder "config/salt", "/srv/salt"
  config.vm.provision :salt do |salt|
    salt.verbose = true
    salt.minion_config = 'config/salt/minions/vagrant.conf'
    salt.run_highstate = true
  end

end
