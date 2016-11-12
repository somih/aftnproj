var updateTimer;
var curGr = 0;

function restart() {
	$.get('index.php?adm=1&f=restart', function(data) {
		alert(data);
	});
}

function setPM() {
	if (!curGr)
		return false;
	$.get("index.php?adm=1&f=set_in_count", {
		'id': curGr,
		'cin': $('#count_in').val()
	},
	function(data) {
		if (data != '')
			showMessage("Ошибка при установке номера:" + data);
			lineUpadeInfo();
	});
}

function setPD() {
	if (!curGr)
		return false;
	$.get("index.php?adm=1&f=set_out_count",{
		'id': curGr,
		'cout': $('#count_out').val()
	},
	function(data){
		if (data != '')
			showMessage("Ошибка при установке номера:" + data);
			lineUpadeInfo();
	});
}

function lineUpadeInfo() {
	$("#line_info_id").text('');
	var gr = $("#channels_tab").getGridParam('selrow');
	if (gr == null) {
		return false;
		clearTimeout(updateTimer);
	}
	var row = $("#channels_tab").getRowData(gr);
	curGr = gr;
	var i = 0;
	$.each(row, function(k, val) {
		$("#line_info_id").append(
			'<pre>' + channelsConfigs[i].label + ':' + val + '</pre>');
			i++;
	});
	$.getJSON('index.php?adm=yes&f=line_stat&canal=' + gr, function(data) { // вешаем свой обработчик на
		$('#count_in_now').text(data["PeerNum"]);
		$('#count_out_now').text(data["MyNum"]);
		$('#count_quie').val(data["MyQuie"]);
		if ($('#count_in').val() == '')
			$('#count_in').val(data["PeerNum"]);
			if ($('#count_out').val() == '')
				$('#count_out').val(data["MyNum"]);
	});
	updateTimer = setTimeout(lineUpadeInfo, 2000);
}

function Setup() {	// ###################### ALL
	$(function() {
		$("#panel1").tabs();
		$("#panel2").tabs();
	});
	$('#pane1').tabs({
		fx: {
			opacity: "toggle",
			duration: "fast"
		}
	});
	$('#pane2').tabs({
		fx: {
			opacity: "toggle",
			duration: "fast"
		}
	});
	$('#pane3').tabs({
		fx: {
			opacity: "toggle",
			duration: "fast"
		}
	});
	$('#pane4').tabs({
		fx: {
			opacity: "toggle",
			duration: "fast"
		}
	});
	$('a.button').each(function(i) {
		$(this).hover(
			function() {
				$(this).addClass('ui-state-hover');
			},
			function() {
				$(this).removeClass('ui-state-hover');
			}
		)
		.addClass('ui-state-default ui-corner-all fm-button-icon-left fm-button')
		.append('<span class="ui-icon ui-icon-' + $(this).attr('icon') + '"></span>');
	});
// ######################## Channels
	function channel_edit(){
		var tab = $("#channels_tab");
		var gr = tab.getGridParam('selrow');
		if (gr != null)
			tab.editGridRow(gr, {
				top: 130,
				left: 100,
				width: 700,
				reloadAfterSubmit: true,
				closeAfterEdit: true,
				mtype: "GET",
				beforeShowForm: function() {
					$("#tr_Options").css("display", "");
				}
			});
			return false;
	}

	$("#channels_tab").jqGrid({
		url: 'index.php?adm=1&f=db&tabl=Channels',
		editurl: 'index.php?adm=1&f=db&tabl=Channels',
		datatype: "json",
		width: 200,
		height: 47,
		altRows: true,
		colModel: channelsConfigs,
		sortname: 'ID',
		jsonReader: {
			repeatitems: false,
			id: 0
		},
		viewrecords: true,
		caption: "Линии связи",
		loadError: function(xhr, st, err) {
			alert(st + ':' + err + ':' + xhr.responseText);
		},
		onSelectRow: function(id) {
			lineUpadeInfo();
		},
		ondblClickRow: channel_edit,
		gridComplete: function() {
			$("#channels_tab").setSelection($("#channels_tab").getDataIDs()[0], true);
		}
	});

	$('#line_add').click(function() {
		$("#channels_tab").editGridRow('new', {
			top: 130,
			left: 100,
			width: 700,
			reloadAfterSubmit: true,
			closeAfterAdd: true,
			mtype: "GET"
		});
		return false;
	});

	$('#line_edit').click(channel_edit);
	$('#line_del').click(function() {
		var gr = jQuery("#channels_tab").getGridParam('selrow');
		if (gr != null)
			jQuery("#channels_tab").delGridRow(gr, {
				reloadAfterSubmit: true,
				mtype: "GET"
			});
			return false;
	});

	$("#line_dialog_info").width(300).height(200).slideDown('slow');
	$("#line_dialog_stat").width(200).height(99).slideDown('slow');

// ######################################### Users
	function usrEdit() {
		var tab = $("#users_tab");
		var tabf = $("#folders_tab");
		var gr = tab.getGridParam('selrow');
		if (gr !== null)
			tab.editGridRow(gr, {
				top: 130,
				left: 100,
				width: 600,
				reloadAfterSubmit: true,
				closeAfterEdit: true,
				mtype: "GET",
				beforeShowForm: function(id) {
					$.each(usersConfigs, function(k, v) {
						if (v.edittype === 'select') {
							$('#' + v.name + ' > [value=' + tab.getCell(gr, v.name) + ']', id).get()[0].selected = true;
						}
					})
				}
			});
			return false;
	}

	$("#users_tab").jqGrid({
		url: 'index.php?adm=1&f=db&tabl=Users',
		editurl: 'index.php?adm=1&f=db&tabl=Users',
		datatype: "json",
		width: 595,
		colModel: usersConfigs,
		sortname: 'ID',
		pager: $("#users_tabp"),
		viewrecords: true,
		caption: "Пользователи",
		ondblClickRow: usrEdit,
		scroll: 1,
		jsonReader: {
			id: "0",
			repeatitems: false
		},
		loadError: function(xhr, st, err) {
			alert(st + ':' + err + ':' + xhr.responseText);
		},
		onSelectRow: function(id) {
			if (id !== null) {
				$("#folders_tab").jqGrid('setGridParam', {url: "index.php?adm=1&f=db&tabl=Folders&id=" + id});
				$("#folders_tab").jqGrid('setCaption', "Наборы масок для - " + id).trigger('reloadGrid');
			}
		},
		gridComplete: function(id) {
			$("#users_tab").setSelection($("#users_tab").getDataIDs()[0], true);
		}
	});

	$("#users_tab").jqGrid('navGrid', '#users_tabp', {edit: false, add: false, del: false, search: false, refresh: false})
		.navButtonAdd('#users_tabp', {
			caption: "Добавить",
			buttonicon: "ui-icon-plus",
			onClickButton: function() {
				$("#users_tab").editGridRow('new', {
					top: 130,
					left: 100,
					width: 600,
					reloadAfterSubmit: true,
					closeAfterAdd: true,
					mtype: "GET"
				});
				return false;
			}
		})
		.navButtonAdd('#users_tabp', {
			caption: "Редактировать",
			buttonicon: "ui-icon-pencil",
			onClickButton: usrEdit
		})
		.navButtonAdd('#users_tabp', {
			caption: "Удалить",
			buttonicon: "ui-icon-trash",
			onClickButton: function() {
				var gr = $("#users_tab").getGridParam('selrow');
				if (gr != null)
					$("#users_tab").delGridRow(gr, {
						reloadAfterSubmit: true,
						mtype: "GET"
					});
					return false;
			}
		});
		$('#base_clear').click(function() {
			var gr = jQuery("#users_tab").getGridParam('selrow');
			if (gr != null) {
				$.ajax({
					url: 'index.php?adm=1&f=sqldelbase&user=' + gr,
					type: "POST",
					data: ({user: gr}),
					success: function(month) {
						alert(month);
					}
				});
			}
		});
///////////////////////////////////////////////////////// folderEdit

	function folderEdit() {
		var tab = $("#folders_tab");
		var gr = tab.getGridParam('selrow');
		if (gr != null)
			tab.editGridRow(gr, {
				top: 130,
				left: 100,
				width: 600,
				reloadAfterSubmit: true,
				closeAfterEdit: true,
				mtype: "GET",
				beforeShowForm: function(id) {
					$.each(foldersConfigs, function(k, v) {
						if (v.edittype == 'select') {
							$('#' + v.name + ' > [value=' + tab.getCell(gr, v.name) + ']', id).get()[0].selected = true;
						}
					})
				}
			});
			return false;
	}

	$("#folders_tab").jqGrid({
		url: 'index.php?adm=1&f=db&tabl=Folders',
		editurl: 'index.php?adm=1&f=db&tabl=Folders',
		datatype: "json",
		width: 200,
		colModel: foldersConfigs,
		sortname: 'ID',
		pager: $("#folders_tabp"),
		viewrecords: false,
		caption: "Наборы масок пользователя - ",
		ondblClickRow: folderEdit,
		scroll: 1,
		jsonReader: {
			repeatitems: false,
			id: "0"
		},
		loadError: function(xhr, st, err) {
			alert(st + ':' + err + ':' + xhr.responseText);
		},
		onSelectRow: function(id) {},
		gridComplete: function() {
			$("#folders_tab").setSelection($("#folders_tab").getDataIDs()[0], true);
		}
	});

	$("#folders_tab").jqGrid('navGrid', '#folders_tabp', {edit: false, add: false, del: false, search: false, refresh: false})
		.navButtonAdd('#folders_tabp', {
			caption: "Добавить",
			buttonicon: "ui-icon-plus",
			onClickButton: function() {
				$("#folders_tab").editGridRow('new', {
					top: 300,
					left: 300,
					width: 600,
					reloadAfterSubmit: true,
					closeAfterAdd: true,
					mtype: "GET",
					beforeShowForm: function() {},
					afterShowForm: function() {}
				});
				return false;
			}
		})
		.navButtonAdd('#folders_tabp', {
			caption: "Редактировать",
			buttonicon: "ui-icon-pencil",
			onClickButton: folderEdit
		})
		.navButtonAdd('#folders_tabp', {
			caption: "Удалить",
			buttonicon: "ui-icon-trash",
			onClickButton: function() {
				var gr = $("#folders_tab").getGridParam('selrow');
				if (gr != null)
					$("#folders_tab").delGridRow(gr, {
						reloadAfterSubmit: true,
						mtype: "GET"
					});
				return false;
			}
		});

		$('#showdbstat').load('index.php?adm=1&f=sql&sql='  + escape('show table status'));
		$('#showdbstat1').load('index.php?adm=1&f=sql&sql=' + escape('SELECT Users.User, count(OutBox.ID) FROM OutBox left join Users on OutBox.User=Users.ID group by OutBox.User'));
		$('#showdbstat2').load('index.php?adm=1&f=sql&sql=' + escape('SELECT User, count(IDMesg) as Telegramms FROM UserBox left join Users on UserBox.IDUser=Users.ID group by User'));
		$('#showdbstat3').load('index.php?adm=1&f=sql&sql=' + escape('SELECT User, count(IDMesg) as Telegramms FROM UserBox left join Users on UserBox.IDUser=Users.ID where UserBox.Status=0 group by User'));

		$('#set_speed_bt').click(function() {
			var s = $('#speed_select').val();
			$('#dbstat').load('index.php?adm=1&f=set_speed&speed=' + s);
		});
	}

$(document).ready(Setup);