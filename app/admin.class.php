<?php

class TAftnAdmin
{

    var $tablesConfigs;
    var $link;
    var $Conf;

    function TAftnAdmin()
    {
        $this->tablesConfigs['Channels'] = '[
            {"name":"ID",     "label":"Номер",    "width":50,"editable":false,"editoptions":{"readonly":true,"size":10}},
            {"name":"Name",   "label":"Имя",      "width":90,"editable":true, "editoptions":{"size":3},"formoptions":{"elmsuffix":"Обозначение канала" }},
            {"name":"State",  "label":"Состояние",           "editable":true, "editoptions":{"value":{"0":"OFF","1":"PM","2":"ON"}},"edittype":"select","formatter":"select"},
            {"name":"Options","label":"Опции",               "editable":true, "edittype":"textarea", "hidden":true,"fixed":true,"formatter":"","formoptions":{"label":"Настройки"} ,"editoptions": {"rows":"20","cols":"80","defaultValue":"ProtocolType=AFTN\nPortType=COM\nMeteo=1\nMyCanelNameRU=МКГ\nMyCanelNameEN=MKG\nPeerCanelNameRU=КМГ\nPeerCanelNameEN=KMG\nMyAddressRU=УКМЕЫМАА\nMyAddressEN=UKMEYMAA\nPeerAddressRU=УКККЫФЫЬ\nPeerAddressEN=UKKKYFYX\nRuProtocol=1\nDebugLevel=10\nCodeSet=KOI7\nChSendIn=01,21,41\nChWaitFrom=3\nChWaitTo=3\nDigits=4\nOldProtocol=0\nAutoCorrectPDNum=0\nMyAdrMasks=UKMEYMAA\n"}}
        ]';
        $this->tablesConfigs['Users'] = '[
            {"name":"ID",    "label":"Номер",              "width":10,"hidden":false},
            {"name":"User",  "label":"Имя",                "width":50,"editable":true},
            {"name":"Statut","label":"Режим",              "width":40,"editable":true,"formatter":"select","edittype":"select", "editoptions":{"value":{"0":"OFF","1":"PM","2":"ON"}}},
            {"name":"Pass",  "label":"Пароль",             "width":50,"editable":true},
            {"name":"Addr",  "label":"Исходящий<br>адресс","width":60,"editable":true},
            {"name":"Masks", "label":"Фильтр<br>входящих",            "editable":true,"edittype":"textarea", "editoptions": {"rows":"10","cols":"60"}},
            {"name":"Level", "label":"Уровень<br>доступа", "width":90,"editable":true,"formatter":"select","edittype":"select","editoptions":{"value":{"0":"Пользователь","1":"Администратор"}}},
            {"name":"Store", "label":"Хранение<br>данных", "width":60,"editable":true}
        ]';
        $this->tablesConfigs['Folders'] = '[
            {"name":"ID",        "width":20, "hidden":true},
            {"name":"IDUser",    "label":"Пользователь","editable":true},
            {"name":"FolderName","label":"Название","width":60,"editable":true},
            {"name":"Masks",     "label":"Маска",   "width":60,"editable":true}
        ]';
        $this->Conf['Channels'] = json_decode('{"conf":' . $this->tablesConfigs['Channels'] . '}')->conf;
        $this->Conf['Users'] = json_decode('{"conf":' . $this->tablesConfigs['Users'] . '}')->conf;
        $this->Conf['Folders'] = json_decode('{"conf":' . $this->tablesConfigs['Folders'] . '}')->conf;

        $opts = array(
            'host' => 'localhost',
            'user' => 'ssc',
            'pass' => '!QAZxsw2',
            'db' => 'aftn',
            'charset' => 'utf8'//latin1'
        );
        $this->db = new SafeMySQL($opts);
    }
    
    function ajax_restart()
    {
        echo system('sudo /opt/aftnweb/restart ');
        //system('sudo -l');
    }

    function ajax_set_speed()
    {
        $id = escapeshellcmd($_GET['speed']);
        if ((!is_numeric($id)) || ($id < 0))
            return;
        echo system('sudo /opt/aftnweb/bin/set_speed ' . $id);
        //system('sudo -l');
    }

    function ajax_sql()
    {
        $result = mysql_query($_GET['sql']) or Die(mysql_error());
        $numfields = mysql_num_fields($result);
        echo "<table width='100%' border=1>\n<tr>";
        for ($i = 0; $i < $numfields; $i++) // Header
        {
            echo '<th>' . mysql_field_name($result, $i) . '</th>';
        }
        echo "</tr>\n";
        while ($row = mysql_fetch_row($result)) // Data
        {
            echo '<tr><td>' . implode($row, '</td><td>') . "</td></tr>\n";
        }
        echo "</table>\n";
    }

    function ajax_sqldelbase()
    {
        $user = $_GET['user'];
        $username = q('SELECT User FROM Users WHERE ID=' . $user);
        $store = q('SELECT Store FROM Users WHERE ID=' . $user);
        $store_days = $store * 30;
        $s0 = q('SELECT NOW() - INTERVAL ' . $store_days . ' DAY;');
        $s1 = q('DELETE FROM InBox where DateIn <= (NOW() - INTERVAL ' . $store_days . ' DAY);');
        $s2 = q('DELETE FROM OutBox where DataSended <= (NOW() - INTERVAL ' . $store_days . ' DAY) and User=' . $user . ';');
        $s3 = q('DELETE FROM UserBox where IDMesg <= (SELECT ID FROM InBox where DateIn > (NOW() - INTERVAL ' . $store_days . ' DAY) LIMIT 1) and IDUser=' . $user . ';');
        echo 'Сообщения пользователя - "' . $username . '" удалены, заканчивая датой ' . $s0;
    }

    function ajax_db()
    {
        if (!isset($_GET['tabl']))
        {
            die("Не указано имя таблицы");
            exit;
        }
        $tabl = $_GET['tabl']; // get the requested page
        if (!isset($_GET['oper']))
        {
            $where = "";
            $page = $_GET['page']; // get the requested page
            $limit = $_GET['rows']; // get how many rows we want to have into the grid
            $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
            $sord = $_GET['sord']; // get the direction
            if (!$sidx)
                $sidx = 1; // connect to the database
            if (($tabl == "Folders") and (isset($_GET['id'])))
            {
                $where = "where IDUser =" . $_GET['id'];
            }
            $SQL = "SELECT COUNT(*) FROM $tabl $where ORDER BY ID";
            $count = q($SQL);
            if ($count > 0)
            {
                $total_pages = ceil($count / $limit);
                if ($page > $total_pages)
                    $page = $total_pages;
                $start = $limit * $page - $limit; // do not put $limit*($page - 1)
                $SQL = "SELECT * FROM $tabl $where ORDER BY $sidx $sord LIMIT $start , $limit";
                $responce->rows = q($SQL);
                $responce->page = $page;
                $responce->total = $total_pages;
                $responce->records = $count;
            } else
            {
                $responce->rows = array();
                $responce->page = 0;
                $responce->total = 0;
                $responce->records = 0;
            }
            echo json_encode($responce);
            return;
        }
        if ($_GET['oper'] == 'del')
        {
            q('DELETE FROM `' . $tabl . '` WHERE ID=' . $_GET['id']);
            return;
        }
        elseif ($_GET['oper'] == 'add')
        {

            if (($tabl == "Folders") and (isset($_GET['id'])))
            {
                $where = "where IDUser =" . $_GET['id'];
                q("INSERT INTO `" . $tabl . "` set ID=DEFAULT, IDUser= DEFAULT");
            }
            else
            {
                q("INSERT INTO `" . $tabl . "` set ID=DEFAULT");
            }
            $ID = mysql_insert_id();
        }
        elseif ($_GET['oper'] == 'edit')
        {
            $ID = $_GET['id'];
        }
        $s = '';
        foreach ($this->Conf[$tabl] as $conf)
        {
            if (isset($_GET[$conf->name]))
            {
                if ($s != '')
                    $s = $s . ',';
                $s = $s . '`' . $conf->name . '`=\'' . mysql_escape_string($_GET[$conf->name]) . '\'';
            }
        }
        q('update `' . $tabl . '` SET ' . $s . ' WHERE ID=' . $ID);
    }

    function ajax_dbstat()
    {
        echo var_dump(q('show table status'));
    }

    function ajax_line_stat()
    {
        $resp = Array();
        $canal = $_GET['canal'];
        if ((!is_numeric($canal)) || ($canal < 0))
            return;
        $resp["PeerNum"] = q('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="PeerNum"');
        $resp["MyNum"] = q('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="MyNum"');
        $resp["MyQuie"] = q('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="Queue_Out_numLast"') - q('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="Queue_Out_numFirst"');
        echo json_encode($resp);
    }

    /** установка номера передатчика */
    function ajax_set_out_count()
    {
        $id = $_GET['id'];
        if ((!is_numeric($id)) || ($id < 0))
            return;
        $cout = $_GET['cout'];
        if ((!is_numeric($cout)) || ($cout < 0))
            return;
        $res = q('REPLACE  Storage SET Canal=' . $id . ',KeyName="MyNum",Val=' . $cout);
        if (is_array($res))
        { //если вернул массив то все нормальн
            echo('');
        }
        else
        {
            echo $res;
        }
    }

    /** установка номера приемника */
    function ajax_set_in_count()
    {
        $id = $_GET['id'];
        if ((!is_numeric($id)) || ($id < 0))
            return;
        $cin = $_GET['cin'];
        if ((!is_numeric($cin)) || ($cin < 0))
            return;
        $res = q('REPLACE  Storage SET Canal=' . $id . ',KeyName="PeerNum",Val=' . $cin);
        if (is_array($res))
        { //если вернул массив то все нормальн
            echo "";
        }
        else
        {
            echo $res;
        }
    }

    /** выдача логов */
    function ajax_load_log()
    {
        global $php_errormsg;
        $id = escapeshellcmd($_GET['id']);
        if (isSet($_GET['date']))
        {
            $date = escapeshellcmd($_GET['date']);
        }
        else
        {
            $date = date('Y-m-d');
        }
        if ((!is_numeric($id)) || ($id < 0))
            return;
        echo 'Загрузка отчета канала ' . $id;
        $fn = '/opt/aftnweb/log/aftn' . $id . '-' . $date . '.log';
        if (!file_exists($fn))
            die('Не найден файл:' . $fn);
        $f = fopen($fn, 'rb') or die('Ошибка открытия файла:' . $fn . ' : ' . $php_errormsg);
        $level = '';
        $colors = '';
        echo '<pre>';
        while (!feof($f))
        {
            $buffer = fgets($f, 4096);
            $buffer = trim($buffer);
            if ($buffer == '')
                continue;
            $p = strpos($buffer, ' :lvl=');
            if ($p)
            {
                $level = $buffer[$p + 6];
                if ($level == '0')
                    echo iconv('koi8-r', 'utf-8', '<small><b>' . $buffer . '</b></small><br>');
            } else
            {
                if ($level == '0')
                    echo iconv('koi8-r', 'utf-8', $buffer . '<br>');
            }
        }
        echo '</pre>';
        fclose($f);
    }

    /** выдача файла потока приема */
    function ajax_load_stream()
    {
        global $php_errormsg;
        $id = escapeshellcmd($_GET['id']);
        if ((!is_numeric($id)) || ($id < 0))
            return;
        echo 'Загрузка отчета канала ' . $id;
        $fn = '/opt/aftnweb/log/aftn' . $id . '-' . date('Y-m-d') . '_in.log';
        if (!file_exists($fn))
            die('Не найден файл:' . $fn);
        $f = fopen($fn, 'rb') or die('Ошибка открытия файла:' . $fn . ' : ' . $php_errormsg);
        $level = '';
        echo '<pre>';
        while (!feof($f))
        {
            $buffer = fgets($f, 4096);
            echo iconv('koi8-r', 'utf-8', $buffer);
        }
        echo '</pre>';
        fclose($f);
    }

    function ajax_js_conf()
    {
        echo "var channelsConfigs=" . $this->tablesConfigs['Channels'];
        echo ";var usersConfigs=" . $this->tablesConfigs['Users'];
        echo ";var foldersConfigs=" . $this->tablesConfigs['Folders'];
    }

}