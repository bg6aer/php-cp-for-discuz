#ǰ��
�������ǻ��� Discuz x3 �������ʵ�֡�ʵ����ԭ��PDO ��(db_driver_pdomysql.php)��PdoProxy��(db_driver_pdoproxymysql.php)������ PdoProxy ����php-cp��ʵ�֣�

#����Ҫ��

- PHP 5.3 + (no zts)
- linux 2.6+
- pdo and redis extension install
- Discuz x3  ��x3.0 ���϶�֧�֣�


# Discuz x3 ʹ��php-cp���ӳ��м��


��װ�������£�

1. ��װ php-cp��������/etc/pool.ini �� ��ο�[https://github.com/swoole/php-cp];
2. ��װphp pdo-mysql ��չ ���ǵ�����php �� ��ο�[http://php.net/manual/zh/ref.pdo-mysql.php ]���������php�����Ѿ�֧��pdo_mysql���������ⲽ;
3. ���� pool_server (�磺 nohup  /usr/local/php/bin/php ./pool_server start );
4. �ϴ�php-cp-for-discuz�е�php�ļ���Ŀ¼���� Discuz�İ�װĿ¼(�ǵñ��ݴ����ǵ��ļ�);
5. �޸�dz�����ļ� config/config_global.php , ���� $_config['db']['driver'] = 'db_driver_pdoproxymysql';
6. ������� http://www.****.com/testx3.php ��*** ��Ϊ�����̳������ ���������������������ݣ�����װ�ɹ���

# Discuz x3 ʹ��ԭ��PDO_MYSQL

��װ�������£�

1. ��װphp pdo-mysql ��չ ���ǵ�����php �� ��ο�[http://php.net/manual/zh/ref.pdo-mysql.php ]���������php�����Ѿ�֧��pdo_mysql���������ⲽ;
2. �ϴ�php-cp-for-discuz�е�php�ļ���Ŀ¼���� Discuz�İ�װĿ¼(�ǵñ��ݴ����ǵ��ļ�);
3. �޸�dz�����ļ� config/config_global.php , ���� $_config['db']['driver'] = 'db_driver_pdomysql';
4. ������� http://www.****.com/testx3.php ��*** ��Ϊ�����̳������ ���������������������ݣ�����װ�ɹ���


# �������

�����ʹ���������������������кõĽ�����Լ� QQȺ:309020981  (���ţ�discuz)

---
* ע1��Discuz x3 Ĭ�ϲ�֧��PDO_MYSQL �� ֻ֧�� mysql �� mysqli��
* ע2���޸�dz�����ļ�  config/config_global.php  �ɲο� config/#config_global_php_cp.php 


# License

Apache License Version 2.0 see http://www.apache.org/licenses/LICENSE-2.0.html