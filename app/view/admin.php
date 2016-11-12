<div class="container">
    <div class="row">
        <div class="span12">
            <div class="row">
                <div class="span4 logoblock">
                    <p class="p1"><?= TConfig::$config['title'] ?></p>
                    <p class="p2"><?= TConfig::$config['orguse'] ?></p>
                </div>
                <div class="offset3 span4">
                    <p class="p2">Панель Администрирования</p>
                    <a href="index.php" class="btn right"><i class="icon-off"></i> Выход</a>
                </div>
            </div>
            <div class="row">
                <div class="span11">
                    <div class="row">
                        <div id="panel1">
                            <ul>
                                <li><a href="#pane-1">Каналы</a></li>
                                <li><a href="#pane-2">Пользователи</a></li>
                                <li><a href="#pane-3">Настройки</a></li>
                                <li><a href="#pane-4">Статистика</a></li>
                            </ul>
                            <!-- ======================================================================= -->
                            <div id="pane-1">
                                <h4>Каналы</h4p>
                                <div class="left">
                                    <div class="row">
                                        <div class="span4">
                                            <div id="line_dialog_select" class="left">
                                                <table id="channels_tab" class="scroll" cellpadding="0" cellspacing="0">
                                                </table>
                                            </div>
                                            <div id="line_dialog_stat">
                                                <table class="table">
                                                    <tr><td>Передатчик:</td><td id=stat_out>*</td></tr>
                                                    <tr><td>В очереди:</td><td><input id=count_quie size=10></td></tr>
                                                    <tr><td>Передано:<span id=count_out_now></span></td><td><input id=count_out size=10><input type=button value="OK" onclick="setPD();"></td></tr>
                                                    <tr><td colspan=2><hr></td></tr>
                                                    <tr><td>Приемник:</td><td id=stat_in>*</td></tr>
                                                    <tr><td>Принято: <span id=count_in_now></span></td><td><input id=count_in size=10><input type=button value="OK" onclick="setPM();"></td></tr>
                                                </table>
                                                <span id="line_stat_id" class=""></span>
                                            </div>
                                            <a href="#" id="line_add" class="btn"><i class="icon-plus"></i>Добавить</a>
                                            <a href="#" id="line_edit" class="btn"><i class="icon-pencil"></i>Редактировать</a>
                                            <a href="#" id="line_del" class="btn"><i class="icon-trash"></i>Удалить</a>
                                        </div>
                                    </div>
                                </div>
                                <div id="line_dialog_info" class="left ui-widget ui-widget-content ui-corner-all">
                                    <span id="line_info_id"></span>
                                </div>
                            </div>
                            <!-- ======================================================================= -->
                            <div id="pane-2">
                                <h4>Пользователи</h4>
                                <span id="panel">
                                    <div class="left">
                                        <div id="users_tabp"></div>
                                        <table id="users_tab" class="scroll table" cellpadding="0" cellspacing="0"></table>
                                        <a href="#" id="base_clear" class="btn"><i class="icon-chevron-up"></i>Очистить информацию выбранного пользователя</a>
                                    </div>
                                    <div class="right">
                                        <div id="folders_tabp"></div>
                                        <table id="folders_tab" class="scroll table" cellpadding="0" cellspacing="0"></table>
                                    </div>
                                </span>
                            </div>
                            <!-- ======================================================================= -->
                            <div id="pane-3">
                                <h4>Системные утилиты</h4>
                                <div id="panel2">
                                    <ul>
                                        <li><a href="#panel21">Скорость канала</a></li>
                                        <li><a href="#panel22">Системное время</a></li>
                                        <li><a href="#panel23">Управление службой aftnweb</a></li>
                                        <li><a href="#panel24">Статус базы данных</a></li>
                                    </ul>
                                    <div id="panel21">
                                        <p><strong>Установка скорости телеграфного канала (адаптера).</strong></p>
                                        <div id="set_speed_bt">
                                            <select>
                                                <option>50</option>
                                                <option selected>100</option>
                                                <option>150</option>
                                                <option>200</option>
                                                <option>300</option>
                                                <option>600</option>
                                                <option>1200</option>
                                                <option>2400</option>
                                            </select>
                                        </div>
                                        <div id="set_speed"></div>
                                        <div id="dbstat"></div>
                                    </div>
                                    <div id="panel22">
                                        <p><strong>Установить системное время</strong></p>
                                    </div>
                                    <div id="panel23">
                                        <strong>Изменения вступят в силу только после перезагрузки сервиса aftnweb !</strong>
                                        <a href="#" id="restart_aftnweb" class="btn" onclick="restart();"><i class="icon-refresh"></i>Перезапуск</a>
                                    </div>
                                    <div id="panel24">
                                        <p><strong>Состояние баз данных.</strong></p>
                                        <div id="showdbstat"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- ======================================================================= -->
                            <div id="pane-4">
                                <h4>Информация о количестве телеграмм.</h4>
                                <p><strong>ПЕРЕДАННЫХ</strong></p>
                                <div id="showdbstat1"></div>
                                <p><strong>ПРИНЯТЫХ</strong></p>
                                <div id="showdbstat2"></div>
                                <p><strong>НЕ ПРОСМОТРЕННЫХ</strong></p>
                                <div id="showdbstat3"></div>
                            </div>
                            <!-- ======================================================================= -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="">
                            <div class="h7 left"><?= TConfig::$config['orgdevel'] ?></div>
                            <div class="h7 right"><?= TConfig::$config['ver'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>