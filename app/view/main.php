<div class="all-wrapper">
        <div class="content-wrapper">
            <div class="row">
                <div class="content-inner">
                    <div class="page-header page-header-blue">
                        <div class="header-links hidden-xs">
                            <a href="#" title="Пользователь"><i class="fa fa-user"></i> <span id="user_name"></span></a>
                            <a href="index.php?adm=yes" title="Панель конфигурирования системы"><i class="fa fa-cogs"></i> Настройки</a>
                            <a href="index.php?logout=yes" title="Выход"><i class="fa fa-sign-out"></i> Выход</a>
                        </div>
                        <h1><i class = "fa fa-plane"></i><?= TConfig::$config['title'] ?></h1>
                        <h6><?= TConfig::$config['orguse'] ?></h6>
                    </div>
                    <div class="main-content">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="content-wrapper side-bar-wrapper collapse navbar-collapse">
                                    <div class="content-inner">
                                        <div class="page-header page-header-blue">
                                            <div class="row">
                                                <div class="chenals">
                                                    <div class="chenalhead">
                                                        <span>Канал</span>
                                                        <span>Передано</span>
                                                        <span>Принято</span>
                                                    </div>
                                                    <div id="chanels_stat"></div>
                                                    <div id="tips"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="side-bar-wrapper collapse navbar-collapse navbar-ex1-collapse">
                                    <div class="relative-w">
                                        <ul class="side-menu">
                                            <li>
                                                <a href="#" id="but_inbox">
                                                    <i class="fa fa-envelope-o"></i> Входящие
                                                    <span id="counts_InBox" class="badge pull-right"></span>
                                                    <span id="counts_InBoxNew" class="badge pull-right alert-animated"></span>
                                                </a>
                                            </li>
                                            <li>
                                                <ul id="folder_user">
                                                </ul>
                                            </li>
                                            <li>
                                                <a href="#" id="but_outbox">
                                                    <span id="counts_OutBox" class="badge pull-right"></span>
                                                    <span id="counts_OutBoxWait" class="badge pull-right"></span>
                                                    <i class="fa fa-share-square-o"></i> Исходящие
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" id="but_trash">
                                                    <span id="counts_InBoxDel" class="badge pull-right"></span>
                                                    <i class="fa fa-code-fork"></i> Корзина
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="search-box">
                                        <input id="appendedInputButtons" type="text" placeholder="ПОИСК" class="form-control" />
                                        <button class="btn" title="Поиск по критериям" onclick="openInBox(0, 'find')"><i class="fa fa-search"></i><span class="hidden-tablet"> Поиск</span></button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <ul class="nav nav-tabs">
                                    <!-- ----------------------------------------------------------------------- -->
                                    <li class="active">
                                        <a href="#tab_pie_chartPM" id="but_inbox" data-toggle="tab" onclick="openInBox(0);"><i class="fa fa-download"></i> Принятые</a>
                                    </li>
                                    <!-- ----------------------------------------------------------------------- -->
                                    <!--               </li>
                                    <a href="#" id="but_inbox" class="button" func='openInBox(0)' >Входящие <span id="but_inbox_count"></span></a>
                                        <li>
                                          <a href="#" id="but_outbox" icon="folder-collapsed" class="button" func='openOutBox(this)'>
                                    <!--        </a>            -->
                                    <!-- ----------------------------------------------------------------------- -->
                                    <li>
                                        <div class="btn-group">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                                Large button <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#">Action</a></li>
                                                <li><a href="#">Another action</a></li>
                                                <li><a href="#">Something else here</a></li>
                                                <li class="divider"></li>
                                                <li><a href="#">Separated link</a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <!-- ----------------------------------------------------------------------- -->
                                    <li><a href="#tab_pie_chartPMR" id="but_outbox" data-toggle="tab" onclick="openInBox(-1);"> <i class="fa fa-trash"></i></a></li>
                                    <!-- ----------------------------------------------------------------------- -->
                                    <li>
                                        <div class="side-bar-wrapper collapse navbar-collapse navbar-ex1-collapse">
                                            <div class="search-box pull-right">
                                                <input id="appendedInputButtons" type="text" placeholder="ПОИСК" class="form-control" />
                                            </div>
                                        </div>
                                    </li>
                                    <!-- ----------------------------------------------------------------------- -->
                                    <li class="pull-right"><a href="#tab_pie_chartPDR" data-toggle="tab"> <i class="fa fa-trash"></i></a></li>
                                    <!-- ----------------------------------------------------------------------- -->
                                    <li class="pull-right"><a href="#tab_pie_chartPD"  data-toggle="tab" onclick="openOutBox(this);"><i class="fa fa-upload"></i> Переданые</a></li>
                                    <!-- ----------------------------------------------------------------------- -->
                                </ul>
                                <div class="tab-content bottom-margin">
                                    <div class="tab-pane active" id="tab_pie_chartPM">
                                        <div class="widget-content-white glossed">
                                            <div class="shadowed-bottom">

                                                <div id="main" class="spanmain table-bordered">
                                                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper form-inline" role="grid">
                                                        <div id="tlg_list_div" c lass="resize ui-widget ui-widget-content ui-corner-all">
                                                            <div id=tlg_page></div>
                                                            <table id="tlg_list" class="scroll" cellpadding="0" cellspacing="0"></table>
                                                        </div>

                                                        <table class="table table-striped table-bordered table-hover datatable dataTable" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                                                            <thead>
                                                                <tr role="row">
                                                                    <th class="sorting_disabled" role="columnheader" rowspan="1" colspan="1" aria-label="" style="width: 16px;">
                                                            <div class="checkbox">
                                                                <input type="checkbox">
                                                            </div>
                                                            </th>
                                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="ID: activate to sort column ascending" style="width: 19px;">Время получения</th>
                                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Country: activate to sort column ascending" style="width: 125px;">Отправитель</th>
                                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Balance: activate to sort column ascending" style="width: 69px;">Время отправления</th>
                                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 66px;">Получатель</th>
                                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 66px;">Заголовок</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody role="alert" aria-live="polite" aria-relevant="all">
                                                                <?php
                                                                for ($i = 0; $i < 10; $i++) {
                                                                    ?>
                                                                    <tr class="even">
                                                                        <td><div class="checkbox"><input type="checkbox"></div><i class="fa fa-eye"></i></td>
                                                                        <td>10:23:16 08/11/2013</td>
                                                                        <td>УККАЯЦДД</td>
                                                                        <td class="text-right ">170830</td>
                                                                        <td>UKDDEUSX UKDDUMKX</td>
                                                                        <td class="text-right ">ТКА071 0839 ГГ</td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                        <div class="row">
                                                            <div class="col-sm-12">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="pull-left">
                                                        <div class="dataTables_info" id="DataTables_Table_0_info" style="margin-top: 18px;">Showing 1 to 10 of 100 entries</div>
                                                    </div>
                                                    <div class="pull-right">
                                                        <div class="dataTables_paginate paging_bootstrap">
                                                            <ul class="pagination pagination-sm">
                                                                <li class="prev disabled"><a href="#"><i class="fa fa-angle-double-left"></i> Previous</a></li>
                                                                <li class="active"><a href="#">1</a></li>
                                                                <li><a href="#">2</a></li>
                                                                <li><a href="#">3</a></li>
                                                                <li><a href="#">4</a></li>
                                                                <li><a href="#">5</a></li>
                                                                <li class="next"><a href="#">Next <i class="fa fa-angle-double-right"></i></a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab_pie_chartPD">
                                        <div class="shadowed-bottom">
                                            <div class="widget-content-white glossed">
                                                <div id="main" class="spanmain table-bordered">
                                                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper form-inline" role="grid">

                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="tab_table">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-toolbar">
                                    <div class="btn-group">
                                        <?php
//if ($_SESSION[PM]   ["level"] != 1)
//if ($aftn->PMUser() == 2)
//{
                                        ?>
                                        
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" title="Создать сообщение"><i class="fa fa-file-text"></i> Создать</button>
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" title="Ответить адресату"><i class="fa fa-share-alt"></i> Ответить</button>
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" title="Переслать выделенное сообщение"><i class="fa fa-pencil"></i> Переслать</button>
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" title="Печатать выделенное сообщение"><i class="fa fa-print"></i></button>
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" title="Перенести выделенное сообщение в корзину"><i class="fa fa-trash"></i></button>


<button id="createTlg" type="button" class="btn btn-default btn-lg" onclick="newTlg();" title="Создать сообщение"><i class="fa fa-file-text"></i> Создать</button>
<button id="responceTlg" type="button" class="btn btn-default btn-lg" onclick="responceTlg();"title="Создать адресату"><i class="fa fa-share-alt"></i> Ответить</button>
<button id="asNewTlg" type="button" class="btn btn-default btn-lg" onclick="asNewTlg();" title="Переслать выделенное сообщение"><i class="fa fa-pencil"></i> Переслать</button>
<button id="printTlg" type="button" class="btn btn-default btn-lg" onclick="printTlg();" title="Печатать выделенное сообщение"><i class="fa fa-print"></i></button>
<button id="delTlg" type="button" class="btn btn-default btn-lg" onclick="delTlg();" title="Перенести выделенное сообщение в корзину"><i class="fa fa-trash"></i></button>

                                    </div>
                                </div>

                                <div class="tab-pane active" id="tab_pie_chartPM">
                                    <div class="widget-content-white glossed">
                                        <div class="shadowed-bottom">
                                            <div class="left">
                                                <pre id="tlg_take"></pre>
                                            </div>
                                            <div>
                                                <pre id="tlg_head"></pre>
                                            </div>
                                            <pre id="tlg_text" class="mesg_txt">text</pre>
                                        </div>
                                    </div>
                                </div>
                                <span id="sound"></span>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="content-inner">
                    <div class="h7 left"><?= TConfig::$config['orgdevel'] ?></div>
                    <div class="h7 right"><?= TConfig::$config['version'] ?></div>
                </div>
            </div>
        </div>
  
</div>

<?php
include 'dialog.php';
include 'message.php';
?>