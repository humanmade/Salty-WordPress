# -*- mode: ruby -*-
# vi: set ft=ruby :

# 'projects' and 'logs' directories are ignored in the repo, so let's make sure they exist
FileUtils.mkdir_p(File.dirname(__FILE__)+'/projects')
FileUtils.mkdir_p(File.dirname(__FILE__)+'/projects/default')
FileUtils.mkdir_p(File.dirname(__FILE__)+'/logs')

Vagrant.configure("2") do |config|

  vagrant_version = Vagrant::VERSION.sub(/^v/, '') 

	config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--memory", 1024]
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end

  config.vm.box = "puphpet/ubuntu1404-x64"

  config.vm.hostname = "salty-wordpress"
  config.vm.network :private_network, ip: "192.168.51.11"

  config.ssh.forward_agent = true

  config.vm.synced_folder "config", "/home/vagrant/config"
  config.vm.synced_folder "logs", "/srv/logs"
  config.vm.synced_folder "config/salt", "/srv/salt"

  config.vm.synced_folder "projects", "/srv/www"

  if File.exists?(File.join(File.dirname(__FILE__),'Customfile')) then
    eval(IO.read(File.join(File.dirname(__FILE__),'Customfile')), binding)
  end

  config.vm.provision :salt do |salt|
    salt.verbose = true
    salt.bootstrap_options= "-F -c /tmp -P"
    salt.minion_config = 'config/salt/minions/vagrant.conf'
    salt.run_highstate = true
  end

end
