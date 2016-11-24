<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: db_driver_mysqli.php 33333 2013-05-28 09:10:48Z kamichen $
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class db_driver_pdomysql {

    var $tablepre;
    var $version = '';
    var $drivertype = 'pdo_mysql';
    var $querynum = 0;
    var $slaveid = 0;
    var $curlink;
    var $link = array();
    var $config = array();
    var $sqldebug = array();
    var $map = array();
    var $rowCount = 0;

    function db_mysql($config = array()) {
        if (!empty($config)) {
            $this->set_config($config);
        }
    }

    function set_config($config) {
        $this->config = &$config;
        $this->tablepre = $config['1']['tablepre'];
        if (!empty($this->config['map'])) {
            $this->map = $this->config['map'];
            for ($i = 1; $i <= 100; $i++) {
                if (isset($this->map['forum_thread'])) {
                    $this->map['forum_thread_' . $i] = $this->map['forum_thread'];
                }
                if (isset($this->map['forum_post'])) {
                    $this->map['forum_post_' . $i] = $this->map['forum_post'];
                }
                if (isset($this->map['forum_attachment']) && $i <= 10) {
                    $this->map['forum_attachment_' . ($i - 1)] = $this->map['forum_attachment'];
                }
            }
            if (isset($this->map['common_member'])) {
                $this->map['common_member_archive'] = $this->map['common_member_count'] = $this->map['common_member_count_archive'] = $this->map['common_member_status'] = $this->map['common_member_status_archive'] = $this->map['common_member_profile'] = $this->map['common_member_profile_archive'] = $this->map['common_member_field_forum'] = $this->map['common_member_field_forum_archive'] = $this->map['common_member_field_home'] = $this->map['common_member_field_home_archive'] = $this->map['common_member_validate'] = $this->map['common_member_verify'] = $this->map['common_member_verify_info'] = $this->map['common_member'];
            }
        }
    }

    function connect($serverid = 1) {

        if (empty($this->config) || empty($this->config[$serverid])) {
            $this->halt('config_db_not_found');
        }

        $this->link[$serverid] = $this->_dbconnect(
                $this->config[$serverid]['dbhost'], $this->config[$serverid]['dbuser'], $this->config[$serverid]['dbpw'], $this->config[$serverid]['dbcharset'], $this->config[$serverid]['dbname'], $this->config[$serverid]['pconnect']
        );
        $this->curlink = $this->link[$serverid];
    }

    function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect, $halt = true) {

        $link = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=$dbcharset", $dbuser, $dbpw);

        if (!$link) {
            $halt && $this->halt('notconnect', $this->errno());
        }

        $link->query("SET NAMES " . $this->config[1]['dbcharset']);

        return $link;
    }

    function table_name($tablename) {
        if (!empty($this->map) && !empty($this->map[$tablename])) {
            $id = $this->map[$tablename];
            if (!$this->link[$id]) {
                $this->connect($id);
            }
            $this->curlink = $this->link[$id];
        } else {
            $this->curlink = $this->link[1];
        }
        return $this->tablepre . $tablename;
    }

    /**
     * PDO ����Ҫ�������
     *  
     */
    function select_db($dbname) {

        return FALSE;
    }

    /**
     * ��ȡһ�����飿
     * @param PDOStatement $query
     * @param type $result_type
     * @return type
     */
    function fetch_array(PDOStatement $query, $result_type = MYSQL_ASSOC) {
        switch ($result_type) {
            case 'MYSQL_ASSOC':
            case MYSQL_ASSOC :
            case 1 :
                $result_type = PDO::FETCH_ASSOC;
                break;

            case 'MYSQL_NUM':
            case MYSQL_NUM :
            case 2:
                $result_type = PDO::FETCH_NUM;
                break;

            default:
                $result_type = PDO::FETCH_BOTH;
                break;
        }
        return $query ? $query->fetch($result_type) : null;
    }

    /**
     * ����һ������
     * @param string $sql
     * @return array
     */
    function fetch_first($sql) {
        return $this->fetch_array($this->query($sql));
    }

    /**
     * ���ص�һ��
     * @param string $sql
     * @return string
     */
    function result_first($sql) {
        return $this->result($this->query($sql), 0);
    }

    /**
     * ִ�в�ѯ
     * @param string $sql
     * @param bool $silent
     * @param bool $unbuffered
     * @return PDOStatement
     */
    public function query($sql, $silent = false, $unbuffered = false) {
        if (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
            $starttime = microtime(true);
        }

        if ('UNBUFFERED' === $silent) {
            $silent = false;
            $unbuffered = true;
        } elseif ('SILENT' === $silent) {
            $silent = true;
            $unbuffered = false;
        }


        if (!$unbuffered) {
            $this->curlink->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,TRUE);
        }

        if (!($query = $this->curlink->query($sql))) {
            if (in_array($this->errno(), array('01002', '08003', '08S01', '08007')) && substr($silent, 0, 5) != 'RETRY') {
                $this->connect();
                return $this->curlink->query($sql, 'RETRY' . $silent);
            }
            if (!$silent) {
                $this->halt($this->error(), $this->errno(), $sql);
            }
        }


        if (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
            $this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink);
        }

        $this->querynum++;

        $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
        if ($cmd === 'SELECT') {
            
        } elseif ($cmd === 'UPDATE' || $cmd === 'DELETE' || $cmd === 'INSERT') {
            $this->rowCount = $query->rowCount(); //��¼��Ӱ�������
        }

        return $query;
    }

    /**
     * ����sqlӰ�������
     * @return int
     */
    function affected_rows() {
        return $this->rowCount;
    }

    /**
     * ��������
     * @return string
     */
    function error() {
        return (($this->curlink) ? $this->curlink->errorInfo() : 'pdo_error');
    }

    /**
     * �����
     * @return int
     */
    function errno() {
        return intval(($this->curlink) ? $this->curlink->errorCode() : 999);
    }

    /**
     * ����һ����¼��ĳ������
     * @param PDOStatement $query
     * @param int $row
     * @return string
     */
    function result(PDOStatement $query, $row = 0) {
        if (!$query || $query->rowCount() == 0) {
            return null;
        }

        return $query->fetchColumn($row);
    }

    /**
     * ��ѯ�ļ�¼������
     * @param PDOStatement $query
     * @return int
     */
    function num_rows(PDOStatement $query) {
        return ($query ? $query->rowCount() : 0);
    }

    /**
     * ����
     * @param PDOStatement $query
     * @return int
     */
    function num_fields(PDOStatement $query) {
        return ($query ? $query->columnCount() : null);
    }

    /**
     * �ͷŲ�ѯ
     * @param PDOStatement $query
     * @return boolean
     */
    function free_result(PDOStatement $query) {

        return true;
    }

    //���ز����¼��id
    function insert_id() {
        return ($id = $this->curlink->lastInsertId()) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    /**
     * ����һ����¼
     * @param PDOStatement $query
     * @return type
     */
    function fetch_row(PDOStatement $query) {

        return ($query ? $query->fetch() : null);
    }

    function fetch_fields(PDOStatement $query) {
        return ($query ? $query->fetch_field() : null);
    }

    function version() {
        if (empty($this->version)) {
            $this->version = $this->curlink->getAttribute(PDO::ATTR_SERVER_VERSION);
        }
        return $this->version;
    }

    /**
     * �ַ���ת��
     * @param string $str
     * @return string
     * ע�⣺ pdo ���� quote() ���ص��ַ����Ǵ������ŵ�,��Ҫȥ�����ҵ����š�
     */
    function escape_string($str) {
        return substr($this->curlink->quote($str), 1, -1);
    }

    /**
     * �رղ�ѯ
     * @return type
     */
    function close() {

        return;
    }

    /**
     * ִ����ֹ��������
     * @param type $message
     * @param type $code
     * @param type $sql
     * @throws PDOException
     */
    function halt($message = '', $code = 0, $sql = '') {
        throw new DbException(var_export($message, true), $code, $sql);
    }

    /**
     * ����DB����
     */
    function beginTransaction() {

        if ($this->curlink->beginTransaction()) {
            return true;
        }
    }

    /**
     * �ύDB����
     */
    function commit() {

        if ($this->curlink->commit()) {
            return true;
        }
    }

    /**
     * �ع�һ��DB����
     *  
     */
    function rollBack() {

        if ($this->curlink->rollBack()) {
            return true;
        }
    }

}

/**
 * ��phpδ���� mysql ԭ������ʱ ��discuz_database.php �ļ������
 * 
 * DB::quote() ����Ҫʹ�õ�mysql_escape_string������
 * 
 * ref : http://php.net/manual/zh/intro.mysql.php
 */
if(!function_exists('mysql_escape_string')){
    
    function mysql_escape_string($str){
               
        return DB::object()->escape_string($str);
    }
            
}