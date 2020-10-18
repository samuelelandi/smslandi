# installation fot CENTOS 7.x
#install required packages
yum -y search epel-release
yum -y info epel-release
yum -y install epel-release
yum -y install epel-release yum-utils
echo "***************************************"
echo "Installation of Kannel for Centos 7."
echo "***************************************"
yum -y install gcc
yum -y install wget
yum -y install help2man
yum -y install libxml2 libxml2-devel
yum -y install gettext-deve
yum -y install libtool openssl-devel svn
yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum install yum-utils
yum-config-manager --enable remi-php73
yum -y install php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo 
cd /usr/src/
mkdir smslandi
cd smslandi
yum -y remove automake
# install a more recent automake
wget https://ftp.gnu.org/gnu/automake/automake-1.8.tar.gz
tar -xzpvf automake-1.8.tar.gz
cd automake-1.8
./configure
make
make install
cd ..
yum -y install libtool
# install old version of bison required from Kannel
wget https://ftp.gnu.org/gnu/bison/bison-1.28.tar.gz
tar -xzpvf bison-1.28.tar.gz 
cd bison-1.28
./configure
make
make install
cd ..
#install Kannel
wget --no-check-certificate https://redmine.kannel.org/attachments/download/322/gateway-1.4.5.tar.gz
tar -xzpvf gateway-1.4.5.tar.gz
cd gateway-1.4.5
./bootstrap.sh
./configure
make
make install
cd addons/opensmppbox
./configure
make
make install
cd /usr/src/smslandi
mkdir /etc/kannel
mkdir /var/log/kannel
cp /usr/src/smslandi/etc/kannel/* /etc/kannel/
rm -f automake-1.8.tar.gz
rm -f bison-1.28.tar.gz
rm -f gateway-1.4.5.tar.gz
#install AGi for Asterisk
cp /usr/src/smslandi/asterisk/agi/smslandidlr.php /var/lib/asterisk/
echo "Installation completed, please check for any error above"
