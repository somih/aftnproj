<?php

class TAftn {

    var $tablesConfigs;
    var $Conf;
    var $userConfig;
    var $db;

    function TAftn() {
        /**
         * ID,DateIn,Status,Canal,Prior,FromAdress,CreateTime,Adress,Head
         */
        $this->tablesConfigs['InBox'] = '[
		{"name":"ID",        "label":"Номер","width":10,"hidden":true},
		{"name":"DateIn",    "label":"Время","width":85,"align":"center","searchoptions":{"DateIn":function(el){$(el).datepicker({showButtonPanel: true});}}},
		{"name":"Status",    "label":"Сост", "width":10,"hidden":true},
		{"name":"Canal",     "label":"Из",   "width":10,"hidden":true},
		{"name":"Prior",     "label":"Приор","width":30,"align":"center","stype":"select","editoptions":{value:":All;KK:KK;GG:GG;FF:FF;DD:DD;CC:CC"}},
		{"name":"FromAdress","label":"От",   "width":55,"align":"center"},
		{"name":"CreateTime","label":"Сост.","width":35,"align":"center"},
		{"name":"Adress",    "label":"Для"},
		{"name":"Head",      "label":"Загол","width":60,"align":"center"}
	]';
        /**
         * ID,DatePost,Status,Prior,Adress,CreateTime,ToCanal,DataSended,Head,FromAdress
         */
        $this->tablesConfigs['OutBox'] = '[
		{"name":"ID",        "label":"Номер","width":50,"hidden":true},
		{"name":"DataPost",  "label":"Время","width":80},
		{"name":"Status",    "label":"Сост", "width":20,"hidden":true},
		{"name":"Prior",     "label":"Приор","width":20},
		{"name":"Adress",    "label":"Для"},
		{"name":"CreateTime","label":"Составлено","width":80},
		{"name":"ToCanal",   "label":"Кан", "hidden":true},
		{"name":"DataSended","label":"Отправлено"},
		{"name":"Head",      "label":"Загол"},
		{"name":"FromAdress","hidden":true}
	]';
        $this->Conf['InBox'] = json_decode('{"conf":' . $this->tablesConfigs['InBox'] . '}');
        $this->Conf['OutBox'] = json_decode('{"conf":' . $this->tablesConfigs['OutBox'] . '}');

        $opts = TConfig::$config;
        $this->db = new SafeMySQL($opts);
    }

    function ajax_get_inbox() {
        $user_id = $this->userConfig['ID'];
        $folder = $_GET['folder'];
        $addr = $_GET['addr'];
        $page = $_GET['page']; // get the requested page
        $limit = $_GET['rows']; // get how many rows we want to have into the grid
        $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
        $sord = $_GET['sord']; // get the direction

        if (!$folder)
            $folder = 0;
        if ($folder == 'undefined')
            $folder = 0;
        if (!$sidx)
            $sidx = 1;        // connect to the database
        $sqlwhere = "";

        if ($addr != '' and $addr != "undefined") {
            $mas = explode(" ", str_replace('*', '%', $addr));
            foreach ($mas as $key => $value) {
                if ($mas[$key][0] == "-") {
                    $sqlwhere .= " AND InBox.FromAdress NOT ";
                } else {
                    $sqlwhere .= " AND InBox.FromAdress ";
                }
                $sqlwhere .= "LIKE '" . str_replace('-', '', $mas[$key]) . "'";
            }
        } else {
        }

        $count = $this->db->getOne("SELECT COUNT(*) FROM UserBox LEFT JOIN InBox on InBox.ID=UserBox.IDMesg where IDUser=?i and IDFolder=?i$sqlwhere;", $user_id, $folder);

        if ($count > 0) {
            $total_pages = ceil($count / $limit);
            if ($page > $total_pages)
                $page = $total_pages;
            $start = $limit * $page - $limit; // do not put $limit*($page - 1)
//            $responce = new stdClass();

$responce ="\"draw\":2,\"recordsTotal:\"$total_pages,\"recordsFiltered\":$count,\"data\":[[";

            $responce.= $page;
            $responce.= $total_pages;
            $responce.= $count;
            $responce = $this->db->getAll("SELECT ID,Canal,Status,DateIn,Head,Adress,Prior,CreateTime,FromAdress FROM UserBox LEFT JOIN InBox on InBox.ID= UserBox.IDMesg where IDUser= ?i and IDFolder= ?i $sqlwhere ORDER BY ?n ?p LIMIT ?i, ?i;", $user_id, $folder, $sidx, $sord, $start, $limit);
        } else {
            $responce = new stdClass();
            $responce->rows = array();
            $responce->page = 0;
            $responce->total = 0;
            $responce->records = 0;
        }
        echo $responce;

        echo json_encode($responce);
        return;
    }

    function ajax_get_outbox() {
        $user_id = $this->userConfig['ID'];
        $folder = 0;
        $page = $_GET['page']; // get the requested page
        $limit = $_GET['rows']; // get how many rows we want to have into the grid
        $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
        $sord = $_GET['sord']; // get the direction
        if (!$sidx)
            $sidx = 1; // connect to the database
        $count = $this->db->getOne("SELECT COUNT(*) FROM OutBox where User=?i", $user_id);

        if ($count > 0) {
            $total_pages = ceil($count / $limit);
            if ($page > $total_pages)
                $page = $total_pages;
            $start = $limit * $page - $limit; // do not put $limit*($page - 1)

            $responce = new stdClass();
            $responce->rows = $this->db->getAll("SELECT ID,DataPost,DataSended,ToCanal,Status,Head,Adress,Prior,CreateTime,FromAdress FROM OutBox WHERE User=" . $user_id . " ORDER BY $sidx $sord LIMIT $start , $limit ");
            $responce->page = $page;
            $responce->total = $total_pages;
            $responce->records = $count;
        } else {
            $responce = new stdClass();
            $responce->rows = array();
            $responce->page = 0;
            $responce->total = 0;
            $responce->records = 0;
        }
        echo json_encode($responce);
        return;
    }

    function ajax_get_tlg() {
        $user_id = $this->userConfig['ID'];
        $ID = $_GET['ID'];
        $baseID = $_GET['base'];
        if ((!is_numeric($ID)) || (!is_numeric($baseID)))
            return;
        if ($baseID == 0) {
            $base = 'InBox';
            $this->db->query('Update UserBox SET Status=1 where IDMesg=?i and Status=0 and IDUser=?i', $ID, $user_id); //меняем статус на просмотреное
        } else {
            $base = 'OutBox';
        }
        $res = $this->db->getOne('SELECT Mesg FROM ?n  where ID=?i', $base, $ID);
        //----------
        $order = array("\r\n", "\n", "\r");
        $replace = '
';
        echo str_replace($order, $replace, $res);
        //----------
    }

    function ajax_get_counts() {
        $user_id = $this->userConfig['ID'];
        $countInBox = $this->db->getOne("SELECT COUNT(*) FROM UserBox where IDUser=" . $user_id . " and IDFolder=0");                 //все принятые(в ящике пользователя)
        $countInBoxNew = $this->db->getOne("SELECT COUNT(*) FROM UserBox where IDUser=" . $user_id . " and IDFolder=0 and Status=0"); //не прочитаны
        $countInBoxDel = $this->db->getOne("SELECT COUNT(*) FROM UserBox where IDUser=" . $user_id . " and IDFolder=-1");             //в удаленных
        $countOutBox = $this->db->getOne("SELECT COUNT(*) FROM OutBox where User=" . $user_id);                                       //все исходящие
        $countOutBoxWait = $this->db->getOne("SELECT COUNT(*) FROM OutBox where User=" . $user_id . " and Status=0");                 //не отправлены
        $str = '{"InBox":"' . $countInBox
                . '","InBoxNew":"' . $countInBoxNew
                . '","InBoxDel":"' . $countInBoxDel
                . '","OutBox":"' . $countOutBox
                . '","OutBoxWait":"' . $countOutBoxWait . '"}';
        echo $str;
    }

    /* ========================================================================================================================== */

    function ajax_get_folder_addrlist() {
        $user_id = $this->userConfig['ID'];
        $res = $this->db->getAll("SELECT FolderName, Masks FROM Folders where IDUser=" . $user_id);   // версия Папок с фильтрами из БД Folders
        if (is_array($res)) { //если вернул массив то все нормальн
            foreach ($res as $key => $value) {
                $res[$key] = $value;
            }
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /*     * ========================================================================================================================== */

    function ajax_post_tlg() {
        $adr = $_GET['adr'];
        $adr = preg_replace("/(\n)/mi", "\r\n", $adr);
        $pri = $_GET['pri'];
        $txt = $_GET['txt'];
        $txt = str_replace("\r", "\n\n\r", $txt);

        $res = $this->db->getAll('INSERT INTO OutBox SET User="' . $this->userConfig['ID'] . '",
			DataPost=Now(),Status=0,Adress="' . $adr . '",CreateTime="' . date('dHi') . '",
			FromAdress="' . $this->userConfig['Addr'] . '",Prior="' . $pri . '",
			Mesg="' . $txt . '"');
        if (is_array($res)) { //если вернул массив то все нормальн
            echo '';
        } else {
            echo $res;
        }
    }

    /**
     * удаление телеграммы из входящих
     */
    function ajax_del_tlg() {
        $user_id = $this->userConfig['ID'];
        $ls = $_GET['id'];
        $res = $this->db->getAll('Update UserBox SET IDFolder=-1 where IDMesg in (' . $ls . ') and IDUser=' . $user_id); //меняем папку на "удаленные" -1;
        if (is_array($res)) { //если вернул массив то все нормальн
            echo '';
        } else {
            echo $res;
        }
    }

    /**
     * загрузка всех шаблонов
     */
    function ajax_load_templ() {
        $res = $this->db->getAll('SELECT TemplName,Address,Template,Prior FROM TemplBook where IDUser=' . $this->userConfig['ID']);
        if (is_array($res)) { //если вернул массив то все нормальн
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /** удаление шаблона */
    function ajax_del_templ() {
        $templ = $_GET['templ'];
        $res = $this->db->getAll('DELETE FROM TemplBook WHERE IDUser=' . $this->userConfig['ID'] . ' and TemplName="' . $templ . '"');
        if (is_array($res)) { //если вернул массив то все нормальн
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /** Добавить шаблон */
    function ajax_add_templ() {
        $name = $_GET['name'];
        $addr = $_GET['addr'];
        $txt = $_GET['txt'];
        $prior = $_GET['prior'];
        $res = $this->db->getAll('REPLACE TemplBook SET IDUser=' . $this->userConfig['ID'] . ', TemplName="' . $name . '",Address="' . $addr . '",Template="' . $txt . '",Prior="' . $prior . '" ');
        if (is_array($res)) { //если вернул массив то все нормальн
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /** загрузка всей адресной книги */
    function ajax_load_addrlist() {
        $res = $this->db->getAll('SELECT AddrName,Address FROM AddrBook where  IDUser=' . $this->userConfig['ID']);
        if (is_array($res)) { //если вернул массив то все нормальн
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /** загрузка всей адресной книги */
    function ajax_load_addr() {
        $res = $this->db->getAll('SELECT FolderName, Masks FROM Folders where IDUser=' . $this->userConfig['ID']); // версия Папок с фильтрами из БД Folders

        if (is_array($res)) { //если вернул массив то все нормальн
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /** удаление адреса */
    function ajax_del_addr() {
        $addr = $_GET['addr'];
        $res = $this->db->getAll('DELETE FROM AddrBook WHERE IDUser=' . $this->userConfig['ID'] . ' and AddrName="' . $addr . '"');
        if (is_array($res)) { //если вернул массив то все нормальн
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /** Добавить адрес в адресную книгу */
    function ajax_add_addr() {
        $name = $_GET['name'];
        $addr = $_GET['addr'];
        $res = $this->db->getAll('REPLACE AddrBook SET IDUser=' . $this->userConfig['ID'] . ', AddrName="' . $name . '",Address="' . $addr . '" ');
        if (is_array($res)) { //если вернул массив то все нормальн
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

// подсчет счетчиков
    function chStat($canal) {
        $resp = Array();
        $resp["PeerNum"] = $this->db->getAll('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="PeerNum"');
        $resp["MyNum"] = $this->db->getAll('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="MyNum"');
        $resp["MyQuie"] = $this->db->getOne('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="Queue_Out_numLast"') - $this->db->getOne('SELECT Val FROM Storage WHERE Canal=' . $canal . ' and KeyName="Queue_Out_numFirst"');
        return $resp;
    }

    /** Выдача статуса каналов */
    function ajax_chahhels_status() {
        $res = $this->db->getAll('SELECT ID,Name,State,PD,PM,PDErr,PMErr,LastText FROM Channels c LEFT JOIN ChannelsStat s on c.ID=s.IDChannel');
        if (is_array($res)) { //если вернул массив то все нормальн
            foreach ($res as $key => $value) {
                $res[$key]['Counts'] = $this->chStat($value['ID']);
            }
            echo json_encode($res);
        } else {
            echo $res;
        }
    }

    /** выдача конфигурации таблицы в виде json */
    function ajax_js_conf() {
        echo "var userConfig=" . json_encode($this->userConfig) . ";\n";
        echo "var InBoxConfigs=" . $this->tablesConfigs['InBox'] . ";\n";
        echo "var OutBoxConfigs=" . $this->tablesConfigs['OutBox'] . ";\n";
    }

    /** Проверка пары имени и пароля и загрузка данных пользователя */
    function authTest($login, $pass) {
        $a = $this->db->getAll('SELECT * FROM Users where User=?s', $login);

        if (!$a)
            return false;
        $a = $a[0];
        if ($a['Pass'] != $pass) {
            return false;
        } else {
            $_SESSION["IDUser"] = $a['ID'];
            $this->userConfig = $a;
            return true;
        }
    }

    function levelUser($luser) {
        $user_id = $this->userConfig['ID'];
        $level = $this->db->getAll("SELECT Level FROM Users where ID=?s", $user_id);
        return $level;
        
    echo $level;    
        
    }

    function PMUser() {
        $user_id = $this->userConfig['ID'];
        $status = $this->db->getAll("SELECT Statut FROM Users where ID=?s", $user_id);
        return $status;
    }

    function viewpage($mode) {
        include 'view/page.php';
    }

}
