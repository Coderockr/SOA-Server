mkdir vendor
cd vendor
wget http://silex.sensiolabs.org/get/silex.phar
git clone http://github.com/rdohms/DMS-Filter.git DMS-Filter
mkdir doctrine
git clone http://github.com/doctrine/common.git doctrine/common
git clone http://github.com/doctrine/dbal.git doctrine/dbal
git clone http://github.com/doctrine/doctrine2.git doctrine/orm
mkdir -p Symfony/Component
git clone https://github.com/symfony/ClassLoader.git Symfony/Component/Classloader
git clone https://github.com/symfony/Validator.git Symfony/Component/Validator