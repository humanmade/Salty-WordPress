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

  vagrant_version = Vagrant::VERSION.sub(/^v/, '') 

	config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--memory", 512]
  end

  config.vm.box = "raring-1310"
  config.vm.box_url = "http://brennovich.s3.amazonaws.com/saucy64_vmware_fusion.box"

  config.vm.hostname = "salty-wordpress"
  config.vm.network :private_network, ip: "192.168.50.10"

  config.ssh.forward_agent = true

  # fixes an issue with latest virtualbox
  if vagrant_version < "1.3.0"
    config.ssh.max_tries = 150
  end

  nfs = false
  config.vm.synced_folder "config", "/home/vagrant/config", :nfs => nfs
  config.vm.synced_folder "projects", "/srv/www", :nfs => nfs

  if File.exists?(File.join(File.dirname(__FILE__),'Customfile')) then
    eval(IO.read(File.join(File.dirname(__FILE__),'Customfile')), binding)
  end
  
  if vagrant_version >= "1.3.0"
    config.vm.synced_folder "databases", "/var/lib/mysql", :mount_options => [ "dmode=777", "fmode=777" ]
  else 
    config.vm.synced_folder "databases", "/var/lib/mysql", :extra => 'dmode=777,fmode=777'
  end

  config.vm.synced_folder "logs", "/srv/logs", :nfs => nfs

  config.vm.synced_folder "config/salt", "/srv/salt"
  config.vm.provision :salt do |salt|
    salt.verbose = true
    salt.minion_config = 'config/salt/minions/vagrant.conf'
    salt.run_highstate = true
  end

end
