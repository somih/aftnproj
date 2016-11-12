<div class="login">
    <div class="span4 well radius shadow">
        <div>
        <h1><?= TConfig::$config['title'] ?></h1>
        <h6><?= TConfig::$config['orguse'] ?></h6>
        </div>
        <legend>Вход в систему</legend>
        <!-- div class="alert alert-error">Неправильный Имя или Пароль!</div -->
        <form class="form-group" method="POST" action="index.php" accept-charset="UTF-8">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon glyphicon glyphicon-user"></div>
                    <input type="text" id="username" class="form-control" name="login" placeholder="Пользователь">
                </div>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon glyphicon glyphicon-lock"></div>
                    <input type="password" id="password" class="form-control" name="passw" placeholder="Пароль">
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember" value="1">Запомнить меня
                </label>
            </div>
            <input type=hidden name=enter value=yes>
            <button type="submit" name="submit" class="btn btn-default"><i class="fa fa-sign-in"></i> Вход</button>
        </form>
        <h6 class="left"><?= TConfig::$config['orgdevel'] ?></h6>
        <h6 class="right"><?= TConfig::$config['version'] ?></h6>
    </div>
</div>
