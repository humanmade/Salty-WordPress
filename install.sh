echo "Downloading Salty-WordPress..."

wget -qO- -O tmp.zip https://github.com/humanmade/Salty-WordPress/archive/v0.1.zip 
unzip tmp.zip -d ~/salty-wordpress 
rm tmp.zip
mv ~/salty-wordpress/*/* ~/salty-wordpress/

#install virtualbox
if ! type "VirtualBox" > /dev/null; then
	echo "Downloading VirtualBox..."
	wget -q0 http://download.virtualbox.org/virtualbox/4.2.12/VirtualBox-4.2.12-84980-OSX.dmg -O ~/Downloads/VirtualBox-4.2.12-84980-OSX.dmg
	open ~/Downloads/VirtualBox-4.2.12-84980-OSX.dmg
	echo "Please run the VirtualBox installer that was opened for you"
	read -p "Press [Enter] when you have finished installing VirtualBox..."
else
	echo "VirtualBox is already installed."
fi

#install vagrant
if ! type "vagrant" > /dev/null; then
	echo "Downloading Vagrant..."
	wget -qO -O ~/Downloads/Vagrant-1.2.7.dmg http://files.vagrantup.com/packages/7ec0ee1d00a916f80b109a298bab08e391945243/Vagrant-1.2.7.dmg
	open ~/Downloads/Vagrant-1.2.7.dmg
	echo "Please run the Vagrant installer that was opened for you"
	read -p "Press [Enter] when you have finished installing Vagrant..."

else
	echo "Vagrant is already installed."
fi

#install salty-vagrant
echo "Installing salty-vagrant"
#vagrant plugin install vagrant-salt

cd ~/salty-wordpress
echo "Booting Vagrant, this can take a few minutes..."
vagrant up

echo "Salty-WordPress has finished installing!

For usage instructions see https://github.com/humanmade/Salty-WordPress/blob/master/README.md"