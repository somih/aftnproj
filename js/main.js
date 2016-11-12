var counts = {
    InBoxNew: -1,
    InBox: -1,
    OutBox: -1,
    OutBoxWait: -1,
    InBoxDel: -1
};
var channelsStats = [];
var selectedRow = null; // текущая выбраная строка
var base = -1; // текущая база -1 нет,0 Inbox,1 OutBox
var folder = 0; // текущая папка в Inbox -1 удаленные, 0 общие входящие, 1...-остальные

/** Telegramm Object */
var tlg = {
    RU_ALF: 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ',
    CyrToLat: 'ABWGDEEVZIJKLMNOPRSTUFHC4???YX??Q',
    LAT_ALF: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    LatToCyr: 'АБЦДЕФГХИЙКЛМНОПЯРСТУЖВЬЫЗ',
    addr: '', // адресная строка
    txt: '', // текст
    prior: '', // приоритет

    /** Проверка адреса */
    chkAddr: function (adr) {
        if (adr)
            this.addr = adr;
        this.addr = $.trim(this.addr); // удаляем лишние пробелы
        this.addr = this.addr.replace(/\n/g, ' ');
        this.addr = this.addr.replace(/\s/g, ' ');
        var addressList = this.addr.split(' ');
        this.addr = '';
        var address = '';
        var ruAddrPat = new RegExp('[' + this.LatToCyr + ']{8}');
        var enAddrPat = new RegExp('[' + this.LAT_ALF + ']{8}');
        var addressLines = 0;
        var addressInLineCount = 0;
        for (var i = 0; i < addressList.length; i++) {
            address = addressList[i];
            if (address === '')
                continue;
            if (address.length !== 8) {
                showMessage('Длинна адреса не равна 8 символам: "' + address + '"');
                return false;
            }
            if ((!ruAddrPat.test(address)) && (!enAddrPat.test(address))) {
                showMessage('Недопустимый символ в адресе: "' + address + '"');
                return false;
            }

            if (addressInLineCount >= 6) {// набрали адресов на всю строку 7шт
                addressInLineCount = 0;
                this.addr = this.addr + "\n";
                addressLines++;
                if (addressLines > 10) {// набралось больше 3х строк
                    showMessage('Слишком много адресов.');
                    return false;
                }
            }
            if (addressInLineCount === 0) {
                this.addr = this.addr + address; // все нормально добавляем 1й адресс в строке
            } else {
                this.addr = this.addr + ' ' + address; // все нормально добавляем адресс
            }
            addressInLineCount++;
        }
        this.addr = this.convertToLat(this.addr);
        this.txt = $.trim(this.txt);
        if ((this.addr === '') || (this.addr.length < 6)) {
            showMessage('Введите адрес' + this.addr);
            return false;
        }
        return true;
    },
    /** проверка допустимости нового сообщения */
    chkMesg: function () {
        if (!this.chkAddr())
            return false;
        if (this.txt === '') {
            showMessage('Введите текст');
            return false;
        }
        if (confirm('Отправить?\nТелеграма будет отправлена в таком виде :\n'
                + this.createMessage())) {
            return true; // выдаем
        }
        return false;
    },
    /** собирает и возвращает сообщение в виде текста */
    createMessage: function () {
        return this.prior + ' ' + this.addr + "\n" + dateNowStr() + ' '
                + userConfig['Addr'] + "\n" + this.txt.wordWrap(69, '\n', 1);
    },
    /** конвертирование строки в АНГЛ */
    convertToLat: function (str) {
        var res = '';
        for (var i = 0; i < str.length; i++) {
            var ind = this.RU_ALF.indexOf(str[i]);
            if (ind >= 0) {
                res = res + this.CyrToLat[ind];
            } else {
                res = res + str[i];
            }
        }
        return res;
    },
    /** конвертирование строки в РУС */
    convertToCyr: function (str) {
        var res = '';
        for (var i = 0; i < str.length; i++) {
            var ind = this.LAT_ALF.indexOf(str[i]);
            if (ind >= 0) {
                res = res + this.LatToCyr[ind];
            } else {
                res = res + this.str[i];
            }
        }
        return res;
    },
    /** отправка сообщения(постановка в очередь) */
    sendMessage: function () {
        $.get("index.php?f=post_tlg", {
            adr: this.addr,
            pri: this.prior,
            txt: this.txt.wordWrap(69, '\n', 1)
        }, function (data) {
            if (data !== '')
                showMessage("Ошибка при передаче телеграммы:" + data);
            updateData();
        });
        return true;
    }
};
/**
 * Сравнение обьектов по всем нужным полям первого обьекта плюс вложеные
 * объекты. Осторожно! уровень вложений не контролируется
 *
 * @param {Object}
 *            a эталон
 * @param {Object}
 *            b с кем сравнивать
 * @return {boolean}
 */
function isEqual(a, b) {
    if (!b)
        return false;
    var res = true;
    for (var item in a) {// функции не сравниваем
        if (typeof (a[item]) === 'function')
            continue;
        if (typeof (a[item]) === 'object') {// рекурсия
            if (!isEqual(a[item], b[item]))
                return false;
        } else {// если хоть одно несовпадение
            if (a[item] !== b[item])
                return false;
        }
    }
    return true;
}

/** обновить */
function updateAll() {
    updateCounts();
    updateChannelStat();
}

/** получение и обновление счетчиков+вывод звука и предупреждения */
function updateCounts() {
    $.getJSON('index.php?f=get_counts', function (c) {
        if (!isEqual(c, counts)) {// это не первый вызов и количество изменилось
            updateData();
            var n = c.InBox - counts.InBox;
            if ((n > 0) && (counts.InBox !== -1)) {
                $('#tips').html('<blink class="ui-state-hover">Новые :' + n + '</blink>');
                setTimeout(function () {
                    $('#tips').html('');
                }, 5000);
                soundPlay();
            }
            counts = c;
            $('#counts_InBoxNew').html(counts.InBoxNew);
            $('#counts_InBox').html(counts.InBox);
            $('#counts_OutBoxWait').html(counts.OutBoxWait);
            $('#counts_OutBox').html(counts.OutBox);
            $('#counts_InBoxDel').html(counts.InBoxDel);
        }
    });
}

function blink(run, err) {
    var color = '';
    var symbol = '';
    if (err === 1) {
        color = 'label-alarm';
    } else {
        color = 'label-norma';
    }
    if (run === 1) {
        symbol = 'onblink';
    } else {
        symbol = 'noblink';
    }
    return color + ' ' + symbol;
}

/** Обновить состояние каналов */
function updateChannelStat() {
    $.getJSON('index.php?f=chahhels_status',
            function (c) {
                if (!isEqual(c, channelsStats)) {// это не первый вызов и количество изменилось
                    var s = '';
                    $.each(c, function (i, item) {
                        var cl = '';
                        if ((userConfig['User'] === 'admin') || (userConfig['User'] === 'oper')) {
                            cl = 'onclick=\'openChannеlDialog(' + i + ')\' ';
                        }
                        /*'<tr ' + cl + ' title=\'' + item['LastText'] + '\' >'
                         + '<td><font size="1">' + item['ID'] + '</font></td>'
                         + '<td><span class="label label-info"> ' + item['Name'] + ' ' + item['State'] + '</span ></td>'
                         + '<td align="right"><span class="label ' + blink(item['PD'], item['PDErr']) + '">' + item['Counts']['MyNum'] + '</td>'
                         + '<td align="right"><span class="label ' + blink(item['PM'], item['PMErr']) + '">' + item['Counts']['PeerNum'] + '</td></tr>';*/
                        s += '<a href = "#">'
                                + '<div id = "chenals' + i + ' "class = "chenal">'
                                + '<i class = "fa fa-random"><font size="1">' + (i + 1) + '</font><span class = "label label-info">' + item['Name'] + ' ' + item['State'] + '</span></i>'
                                + '<i class = "fa fa-upload"><span class = "label label-norma noblink" style="float:right; width:50px;">' + item['PD'] + '</span></i>'
                                + '<i class = "fa fa-download"><span class = "label label-norma noblink" style="float:right; width:50px;">' + item['PM'] + '</span></i>'
                                + '</div>'
                                + '</a>';
                    });
                    $(s).insertAfter($('#chanels_stat'));
                }
                channelsStats = c;
            });
}

/** обновление таблицы */
function updateData() {
    $("#tlg_list").trigger('reloadGrid'); // обновление списка
}

/** воспроизведение звука */
function soundPlay() {
    $('#sound').html('<embed src="sound/bim.wav" hidden="true" autostart="true" loop="false" hidden />');
}

/** вывод предупреждений */
function showMessage(mesg) {// отображает сообщение
    alert(mesg);
}

/** открытие окна канала */
function openChannеlDialog(i) {
    $('#channels_dialog_id').text(channelsStats[i].ID);
    $('#channels_dialog_id').attr('num', i);
    $('#channels_dialog_name').text(channelsStats[i].Name);
    channelDialogSelect(0);
    $('#channels_dialog').dialog('open');
    return false;
}

/**
 * Подгрузка данных при смене вкладки
 *
 * @param {Object}
 *            i номер вкладки
 */
var nsId;
function channelDialogSelect(i) {
    updateChannelStat();
    nsId = $('#channels_dialog_id').text();
    nsNum = $('#channels_dialog_id').attr('num');
    if (i === 0) {
        // ajax_line_stat
        // TODO
        // $(ui.panel).load('index.php?f=load_log&id='+nsId);
        $('#ch_in_count').text(channelsStats[nsNum]['Counts']['PeerNum']);
        $('#ch_in_count_inp').val(channelsStats[nsNum]['Counts']['PeerNum']);
        $('#ch_out_count').text(channelsStats[nsNum]['Counts']['MyNum']);
        $('#ch_out_count_inp').val(channelsStats[nsNum]['Counts']['MyNum']);
    } else
    if (i === 1) {
        // $('#ch_tabs_log_list').load('index.php?f=load_log&id='+nsId);
    } else
    if (i === 2) {
        // $('#ch_tabs_log_list').load('index.php?f=load_log&id='+nsId);
    } else
    if (i === 3) {
        $("#ch_tabs_log_date").datepicker('setDate', new Date());
        $('#ch_tabs_log_list').html('Загрузка');
        $('#ch_tabs_log_list').load('index.php?adm=1&f=load_log&id=' + nsId,
                function () {
                    this.scrollTop = this.scrollHeight;
                });
    } else
    if (i === 4) {
        $('#ch_tabs_stream_list').html('Загрузка');
        $('#ch_tabs_stream_list').load(
                'index.php?adm=1&f=load_stream&id=' + nsId, function () {
                    this.scrollTop = this.scrollHeight;
                });
    }
}

/**
 * обработка нажатия на вкладку.
 *
 * @param {Object}
 *            event
 * @param {Object}
 *            ui
 */
function channelsDialogSelectItem(event, ui) {
    channelDialogSelect(ui.index);
}

function setOutCount() {
    var nsId = $('#channels_dialog_id').text();
    var cout = $('#ch_out_count_inp').val();
    $.get("index.php?adm=1&f=set_out_count", {
        'id': nsId,
        'cout': cout
    }, function (data) {
        if (data !== ' ')
            showMessage("Ошибка при установке номера:(" + data + ")");
        // channelDialogSelect(0);
        $('#channels_dialog').dialog('close');
        updateChannelStat();
    });
}

function setInCount() {
    var nsId = $('#channels_dialog_id').text();
    var cin = $('#ch_in_count_inp').val();
    $.get("index.php?adm=1&f=set_in_count", {
        'id': nsId,
        'cin': cin
    }, function (data) {
        if (data !== ' ')
            showMessage("Ошибка при установке номера:" + data);
        // channelDialogSelect(0);
        $('#channels_dialog').dialog('close');
        updateChannelStat();
    });
}

/** открытие окна настроек */
function showOptions() {
    $('#option_dialog').dialog('open');
    return false;
}

/** открытие окна создания нового сообщения */
function newTlg() {
    $("#new_dialog").dialog({
        modal: true,
        buttons: {
            Ok: function () {
                $.ajax({
                    url: '/path/to/request/url',
                    context: this,
                    success: function (data)
                    {
                        /* Calls involving $(this) will now reference 
                         your "#dialog" element. */
                        $(this).dialog("close");
                    }
                });
            }
        }
    });
    /*
     
     $('#new_dialog').dialog('open');
     return false;
     */
}

/** ответить */
function responceTlg() {
    if (selectedRow !== null) {
        $('#dlg_addr').val($("#cur_from_fromaddr").html());
        $('#dlg_txt').val($("#tlg_text").html());
        $('#new_dialog').dialog('open');
    } else {
        showMessage('Не выбрана телеграмма');
    }
    return false;
}

/** редактировать как новое */
function asNewTlg() {
    if (selectedRow !== null) {
        $('#dlg_addr').val($("#cur_from_addr").html());
        $('#dlg_txt').val($("#tlg_text").html());
        $('#new_dialog').dialog('open');
    } else {
        showMessage('Не выбрана телеграмма');
    }
    return false;
}

/** печать текущего сообщения */
function printTlg() {
    newWin = window.open('', 'printWindow', 'Toolbar=0,Location=0,Directories=0,Status=0,Menubar=1,Scrollbars=0,Resizable=0');
    newWin.document.open();
    newWin.document.write('<style type="text/css">');
    newWin.document.write('#tlg_head, .mesg_txt {font-family:"Courier New" !important;};');
    newWin.document.write('</style>');
    newWin.document.write($('#text_text').html());
    newWin.document.close();
    newWin.print();
}

/** обновление кнопок(добавление иконок и калбеков) */
function updateUi() {
    $('a.button').each(function (i) {
        $(this).prepend(
                '<span class="ui-icon ui-icon-' + $(this).attr('icon')
                + '" >_</span>').click(function () {
            eval($(this).attr('func'));
        });
    });
}

/** отчиска окна отображения телеграммы */
function clearTlg() {
    $('#tlg_head').html('<font size=-3>не выбрано сообщение</font>');
    $('#tlg_take').html('<font size=-3>не выбрано сообщение</font>');
    $("#tlg_text").html('<font size=-3>не выбрано сообщение</font>');
}

/** заполнение данными окна отображения телеграммы */
function updateTlg() {
    selectedRow = $("#tlg_list").getGridParam('selrow');
    if (selectedRow === null) {
        clearTlg();
        return false;
    }
    var row = $("#tlg_list").getRowData(selectedRow);
    if (base === 0) {
        $('#tlg_take').html(
                '<br>'
                + '<span title="Канал"><i class="icon-signal"></i> Канал - ' + row['Canal'] + '</span><br>'
                + '<span title="Передано"><i class="icon-check"></i> ' + row['DateIn'] + '</span>');
        $('#tlg_head').html(
                '<span>' + row['Head'] + '</span><BR>'
                + '<span>' + row['Prior'] + '</span>&nbsp;' + '<span>' + row['Adress'] + '</span><BR>'
                + '<span>' + row['CreateTime'] + '</span>&nbsp;' + '<span>' + row['FromAdress'] + '</span>');
        $("#tlg_text").load(
                'index.php?f=get_tlg&ID=' + row['ID'] + '&base=' + base);
        if (row['Status'] === '0') {
            updateData();
        }
    } else {
        $('#tlg_take').html(
                '<span title="Составлено"><i class="icon-edit"></i> ' + row['DataPost'] + '</span><br>'
                + '<span title="Канал"><i class="icon-signal"></i> Канал - ' + row['ToCanal'] + '</span><br>'
                + '<span title="Передано"><i class="icon-share"></i> ' + row['DataSended'] + '</span>');
        $('#tlg_head').html(
                '<span>' + row['Head'] + '</span><BR>'
                + '<span>' + row['Prior'] + '</span>&nbsp;<span>' + row['Adress'] + '</span><BR>'
                + '<span>' + row['CreateTime'] + '</span>&nbsp;<span>' + row['FromAdress'] + '</span>');
        $("#tlg_text").load(
                'index.php?f=get_tlg&ID=' + row['ID'] + '&base=' + base);
    }
}

/** Удаление текущего сообщения(переместить в корзину) */
function delTlg() {
    if ((base === 0) && (folder !== -1) && (selectedRow !== null)) {
// если мы во входящих и не в удаленных и есть выделение
        s = $("#tlg_list").jqGrid('getGridParam', 'selarrrow').join(',');
        if (s !== '') {
            $.get('index.php?f=del_tlg', {'id': s, base: 0},
            function (data) {
                if (data !== '')
                    showMessage('Ошибка при удалении телеграммы:' + data);
                updateData();
            }
            );
        }
    }
}

/** открыть входящие */
function openInBox(folderId, item) {
    addres = "";
    folder = folderId;
    if (base !== 0) {
        selectedRow = null;
    }
    base = 0;
    $("#tlg_list").GridUnload();
    if (item !== "") {
        addres = '&addr=' + item;
        if (item === "find") {
            //	ID	Canal	DateIn	Head	Adress	Prior	CreateTime	FromAdress	Mesg
            addres = '&p1=' + $('#fs_DateIn').val()
                    + '&p2=' + $('#fs_Head').val()
                    + '&p3=' + $('#fs_Adress').val()
                    + '&p4=' + $('#fs_Prior').val()
                    + '&p5=' + $('#fs_CreateTime').val()
                    + '&p6=' + $('#fs_FromAdress').val()
                    + '&p7=' + $('#fs_TlgText').val();
        }
    }
    var mygrid = $("#tlg_list").jqGrid({
        url: urlS = 'index.php?f=get_inbox&folder=' + folderId + addres,
        datatype: "json",
        colModel: InBoxConfigs,
        sortname: 'DateIn',
        viewrecords: true,
        sortorder: "desc",
        caption: "Телеграммы: Входящие",
        multiselect: true,
     /*   width: 685,
        height: 220,*/
        multikey: "ctrlKey/shiftKey",
        rowList: [10, 30, 50, 100],
        pager: $("#tlg_page"),
        sortable: true,
        jsonReader: {
            repeatitems: false,
            id: "0"
        },
        afterInsertRow: function (row_id, row_data) {
            if (row_data.Status !== 1) {
                var icl, grparam = $.extend({}, $(this).jqGrid("getGridParam"));
                for (icl = 0; icl < grparam.colModel.length; icl++) {
                    $('#tlg_list').jqGrid('setCell', row_id, icl, '', {'color': 'red'});
                }
            }
        },
        gridComplete: function () {
            updateCounts();
            $('#tlg_list > tbody > tr').each(function (i) {
                var td = $(this).children().eq(2);
                if (td.attr('title') === '0') {
                    this.style.color = "#AAAAAA";
                }
            });
            if (selectedRow !== null)
                $("#tlg_list").jqGrid('setSelection', selectedRow);
        },
        onSelectRow: updateTlg,
        loadError: function (xhr, st, err) {
            alert(st + ':' + err + ':' + xhr.responseText);
        },
        beforeSelectRow: function (rowid, e) {
            if (!e.ctrlKey && !e.shiftKey) {
                $("#tlg_list").jqGrid('resetSelection');
            }
            else if (e.shiftKey) {
                var initialRowSelect = $("#tlg_list").jqGrid('getGridParam', 'selrow');
                $("#tlg_list").jqGrid('resetSelection');
                var CurrentSelectIndex = $("#tlg_list").jqGrid('getInd', rowid);
                var InitialSelectIndex = $("#tlg_list").jqGrid('getInd', initialRowSelect);
                var startID = "";
                var endID = "";
                if (CurrentSelectIndex > InitialSelectIndex) {
                    startID = initialRowSelect;
                    endID = rowid;
                }
                else {
                    startID = rowid;
                    endID = initialRowSelect;
                }
                var shouldSelectRow = false;
                $.each($("#tlg_list").getDataIDs(), function (_, id) {
                    if ((shouldSelectRow = id === startID || shouldSelectRow)) {
                        $("#tlg_list").jqGrid('setSelection', id, false);
                    }
                    return id !== endID;
                });
            }
            return true;
        }
    });
    $("#tlg_list").jqGrid('navGrid', '#tlg_page', {edit: false, add: false, del: false, search: false, refresh: true}, {}, {}, {}, {multipleSearch: false});
    clearTlg();
}

/** открыть исходящие */
function openOutBox(but) {
    if (base !== 1)
        selectedRow = null;
    base = 1;
    $("#tlg_list").GridUnload();
    $("#tlg_list").jqGrid({
        url: 'index.php?f=get_outbox',
        datatype: "json",
        colModel: OutBoxConfigs,
        rowNum: 10,
        rowList: [10, 30, 50, 100],
        pager: $("#tlg_page"),
        sortname: 'DataPost',
        viewrecords: true,
        sortorder: "desc",
        caption: "Телеграммы: Исходящие",
        multiselect: true,
        multikey: "ctrlKey/shiftKey",
        sortable: true,
        altRows: true,
        gridview: true,
        jsonReader: {
            repeatitems: false,
            id: "0"
        },
        gridComplete: function () {
            updateCounts();
            $('#tlg_list > tbody > tr').each(function (i) {
                var td = $(this).children().eq(2);
                if (td.attr('title') === '0') {
                    this.style.color = "#AAAAAA";
                }
                if (selectedRow)
                    $("#tlg_list").jqGrid('setSelection', selectedRow);
            });
        },
        onSelectRow: updateTlg,
        loadError: function (xhr, st, err) {
            alert(st + ':' + err + ':' + xhr.responseText);
        }
    });
    clearTlg();
}

function testAjaxResponce(res) {
    try {
        var r = eval(res);
    } catch (err) {
    }
    if (!$.isArray(r))
        showMessage(res);
}

/** загрузка шаблонов */
function loadTemplBook() {
    $.getJSON('index.php?f=load_templ', function (templbook) {
        var s = '<table>';
        $.each(templbook, function (i, item) {
            s += "<tr class=templ_names title='"
                    + item['Address']
                    + ' '
                    + item['Template']
                    + "'><td><a href='#' class='btn speedbut'  title='Удалить'><span class='ui-icon ui-icon-trash'> </span></a></td><td class='combo'>"
                    + item['TemplName']
                    + '</td>'
                    + '<td class="combo" style="display:none">'
                    + item['Address']
                    + '</td>'
                    + '<td class="combo" style="display:none">'
                    + item['Template']
                    + '</td>'
                    + '<td class="combo" style="display:none">'
                    + item['Prior']
                    + '</td></tr>';
        }); // each
        s += '</table>';
        $('#templ_list').html(s);
        // $("tr:nth-child(odd)",).addClass("odd");
        $('.templ_names').click(function (e) {
            if ($(e.target).is('span')) { // кнопка удаления адреса из списка
                confirm('Удалить "' + $('td:eq(1)', this).html() + '"');
                $.get('index.php?f=del_templ', {
                    'templ': $('td:eq(1)', this).html()
                }, function (res) {
                    testAjaxResponce(res);
                    loadTemplBook();
                });
            } else {
                hideBook('templ');
                $('#dlg_addr').val(('td:eq(2)', this).html());
                $('#dlg_txt').val($('td:eq(3)', this).html());
                $('#dlg_prior').val($('td:eq(4)', this).html());
            }
        });
    }); // getJson
}


/** Загрузить  */
function loadFolders() {
    $.getJSON('index.php?f=load_addr', function (addrbook) {
        var s = '<table>';
        $.each(addrbook, function (i, item) {
            s += "<tr class=addr_names title='"
                    + item['Address']
                    + "'><td><a href='#' class='btn speedbut' title='Удалить'><span class='ui-icon ui-icon-trash'> </span></a></td><td class='combo'>"
                    + item['AddrName']
                    + '</td><td class="combo" style="display:none">'
                    + item['Address']
                    + '</td></tr>';
        }); // each
        s += '</table>';
        $('#addr_list').html(s);
        // $("tr:nth-child(odd)",).addClass("odd");
        $('.addr_names').click(function (e) {
            if ($(e.target).is('span')) { // кнопка удаления адреса из списка
                confirm('Удалить "'
                        + $('td:eq(1)', this).html()
                        + '"');
                $.get('index.php?f=del_addr', {
                    addr: $('td:eq(1)', this).html()
                }, function (res) {
                    testAjaxResponce(res);
                    loadAddrBook();
                });
            } else {
                hideBook('addr');
                addrField = $('#dlg_addr');
                addrField.val($.trim(addrField.val()
                        + ' '
                        + $('td:eq(2)', this).html()));
            }
        });
    }); // getJson
}

/** Загрузить адреса */
function loadAddrBook() {
    $.getJSON('index.php?f=load_addrlist', function (addrbook) {
        var s = '<table>';
        $.each(addrbook, function (i, item) {
            s += "<tr class=addr_names title='"
                    + item['Address']
                    + "'><td><a href='#' class='btn speedbut' title='Удалить'><span class='ui-icon ui-icon-trash'></span></a></td><td class='combo'>"
                    + item['AddrName']
                    + '</td><td class="combo" style="display:none">'
                    + item['Address']
                    + '</td></tr>';
        }); // each
        s += '</table>';
        $('#addr_list').html(s);
        // $("tr:nth-child(odd)",).addClass("odd");
        $('.addr_names').click(function (e) {
            if ($(e.target).is('span')) { // кнопка удаления адреса из списка
                confirm('Удалить "'
                        + $('td:eq(1)', this).html()
                        + '"');
                $.get('index.php?f=del_addr', {
                    addr: $('td:eq(1)', this).html()
                }, function (res) {
                    testAjaxResponce(res);
                    loadAddrBook();
                });
            } else {
                hideBook('addr');
                addrField = $('#dlg_addr');
                addrField.val($.trim(addrField.val()
                        + ' '
                        + $('td:eq(2)', this).html()));
            }
        });
    }); // getJson
}

/** Добавить новый шаблон */
function addTempl() {
    var address = $('#dlg_addr').val().toUpperCase();
    var txt = $('#dlg_txt').val().toUpperCase();
    var prior = $('#dlg_prior').val();
    var addrname = $('#input_templ_name').val();
    if (tlg.chkAddr(address)) {
        $.get('index.php?f=add_templ', {
            'name': addrname,
            'addr': address,
            'txt': txt,
            'prior': prior
        }, function (res) {
            testAjaxResponce(res);
            loadTemplBook();
        });
        hideBook('templ');
    }
}

/** Добавить новый адресс */
function addAddr() {
    var address = $('#dlg_addr').val().toUpperCase();
    var addrname = $('#input_addr_name').val();
    if (tlg.chkAddr(address)) {
        $.get('index.php?f=add_addr', {
            'name': addrname,
            'addr': address
        }, function (res) {
            testAjaxResponce(res);
            loadAddrBook();
        });
        hideBook('addr');
    }
}

/** развернуть панель */
function showBook(name) {
    $("#" + name + "_panel").css("margin-left",
            $("#" + name + "_but").position().left - 70).css("top",
            $('#dlg_addr').position().top);
    $("#" + name + "_panel").slideDown("fast").attr('show', 1);
    $("#" + name + "_but > span").removeClass('ui-icon-arrowthickstop-1-s')
            .addClass('ui-icon-arrowthickstop-1-n');
}

/** скрыть панель */
function hideBook(name) {
    $("#" + name + "_panel").slideUp("fast").attr('show', 0);
    $("#" + name + "_but > span").removeClass('ui-icon-arrowthickstop-1-n')
            .addClass('ui-icon-arrowthickstop-1-s');
}

/** Начало работы */
function main() {
    $.jgrid.useJSON = true; // use build JSON.parse for browsers that support it
    $.ajaxSetup({async: true, scriptCharset: "utf-8"});
    $("#user_name").html(userConfig['User']);
    $("#user_addr").html(userConfig['Addr']);
    $.getJSON("index.php?f=get_folder_addrlist", function (data) {
        $.each(data, function (i, item) {
            j = 1;
            for (i in item) {
                if (i === "FolderName") {
                    FolderName = item[i];
                }
                if (i === "Masks") {
                    var Masks = item[i];
                }
            }
            $("<li>").attr("class", "btn").appendTo("#folder_user" + j);
            $("<a>").attr("href", "#").attr("class", "btn span180").click(function () {
                openInBox(0, Masks);
            }).appendTo("#folder_user").html("<span class='pull-left'><i class='icon-folder-open'></i></span><span class='pull-right'>" + FolderName + "</span>");
        });
    }); // версия Папок с фильтрами из БД Folders

    openInBox();

    (function () {
        $('.resize').resizable({
            ghost: true,
            grid: [50, 50],
            handles: 's'
        });
    });

    $('#tlg_list_div').bind('resizestop', function (event, ui) {
        $("#tlg_list").setGridHeight($(this).height() - 80);
    });
    // ################### SendDialog

    (function () {
        $('#new_dialog').dialog({
            autoOpen: false,
            width: 685,
            close: function (event, ui) {
                // TODO:clear filds $('#new_dialog').remove()
                $('.resize').resizable({// востановить хак- не работает z-order
                    ghost: true,
                    grid: [50, 50],
                    handles: 's'
                });
            },
            buttons: {
                "Отправить": function () {
                    tlg.addr = $('#dlg_addr').val().toUpperCase();
                    tlg.txt = $('#dlg_txt').val().toUpperCase();
                    tlg.prior = $('#dlg_prior').val().toUpperCase();
                    if (!tlg.chkMesg())
                        return false;
                    tlg.sendMessage();
                    $(this).dialog('close');
                    return false;
                },
                "Удалить всё": function () {
                    $('#dlg_addr').val('');
                    $('#dlg_txt').val('');
                }
            }
        });
    });
    $('.mesg_txt').focus(function () {
        hideBook('addr');
        hideBook('templ');
    });
    $('#addr_but').click(function () {// типа togle но с учетом других воздействий
        if ($('#addr_panel').attr('show') === 0) {
            showBook('addr');
        } else {
            hideBook('addr');
        }
    });
    $('#templ_but').click(function () {// типа togle но с учетом других воздействий
        if ($('#templ_panel').attr('show') === 0) {
            showBook('templ');
        } else {
            hideBook('templ');
        }
    });
    hideBook('addr'); //
    loadAddrBook(); // версия Папок с фильтрами из входящих
    loadFolders(); // версия Папок с фильтрами из БД Folders
    hideBook('templ'); //
    loadTemplBook();
    // ################### OptionDialog

    (function () {
        $('#option_dialog').dialog({
            autoOpen: false,
            width: 400,
            close: function (event, ui) {
                // TODO:clear filds $('#new_dialog').remove()
            }
        });
    });
    // ################### ChannelsDialog
    (function () {
        $('#channels_dialog').dialog({
            autoOpen: false,
            width: 900,
            height: 800,
            close: function (event, ui) {
                // TODO:clear filds $('#new_dialog').remove()
                $('#ch_tabs').tabs('select', 0);
            }
        });
    });
    (function () {
        $('#ch_tabs').tabs({});
    });
    $('#ch_tabs').bind('tabsselect', channelsDialogSelectItem);
    (function () {
        $("#ch_tabs_log_date").datepicker({
            showOn: "both",
            onSelect: function (dateText, inst) {
                $('#ch_tabs_log_list').load('index.php?adm=1&f=load_log&id=' + nsId + '&date='
                        + dateText, function () {
                            this.scrollTop = this.scrollHeight;
                        });
            }
        });
    });
    updateChannelStat();
    updateUi();
    setInterval(updateCounts, 10000);
    setInterval(updateChannelStat, 10000);
    updateCounts();
    $("#gbox_tlg_list").css('width:100%;');
    $("#gview_tlg_list").css('width:100%;');
    $(".ui-jqgrid-hdiv").css('width:100%;');
    $(".ui-jqgrid-htable").css('width:100%;');
    $(".ui-jqgrid-bdiv").css('width:100%;');
} // end Setup

$(document).ready(main);