<!-- OptionsDialog -->
<div id="option_dialog" title="Настройки">
    Адрес:<input id=addr value="Введите адрес...">
    <br>
    Срочность:
    <select>
    </select>
    <br>
</div>
<!-- ChannelsDialog -->
<div id="channels_dialog" title="Состояние канала">
    Канал связи: (<span id="channels_dialog_id"></span>)&nbsp;<span id="channels_dialog_name"></span><br>
    <div id="ch_tabs">
        <ul>
            <li><a href="#ch_tabs_count">Счетчики</a></li>
            <!-- li><a href="#ch_tabs_send">Передано</a></li>
            <li><a href="#ch_tabs_gets">Принято</a></li -->
            <li><a href="#ch_tabs_log">Журнал</a></li>
            <li><a href="#ch_tabs_stream">Поток</a></li>
        </ul>
        <div id="ch_tabs_count">
            <h2>Счетчики</h2>
            Передано сообщений: <span id="ch_out_count"></span>
            <input id="ch_out_count_inp" class="btn" type="text">
            <a funcz="setOutCount()" class="btn">Применить</a><br>
            Принято сообщений:	<span id="ch_in_count"></span>
            <input id="ch_in_count_inp" type="text">
            <a func="setInCount()" class="btn">Применить</a>
        </div>
        <div id="ch_tabs_send">(Зарезервировано)</div>
        <div id="ch_tabs_gets">(Зарезервировано)</div>
        <div id="ch_tabs_log">
            <input id="ch_tabs_log_date" type="text" value="">
            <div id="ch_tabs_log_list"> </div>
        </div>
        <div id="ch_tabs_stream">
            <div id="ch_tabs_stream_list"> </div>
        </div>
    </div>
</div>