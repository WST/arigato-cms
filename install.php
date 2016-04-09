<?php

error_reporting  (E_ERROR | E_WARNING | E_PARSE);

if ( file_exists ("install.lock") ) die ("Установщик заблокирован<br>Удалите <b>install.lock</b>, чтобы разблокировать");

$error_msg = "";
$war = "";

function get_url ($url)
// Убрать слэшь в конце url-адреса:
{
	while ( substr ($url, -1) === '/' || substr ($url, -1) === '\\' ) $url = substr ($url, 0, -1);
	return $url;
}

while ( isset ($_POST['go']) )
{
  $INFO = array();
  $INFO['db_server'] = trim ($_POST['db_server']);
  $INFO['db_login'] = trim ($_POST['db_login']);
  $INFO['db_password'] = trim ($_POST['db_password']);
  $INFO['db_database'] = trim ($_POST['db_database']);
  $INFO['db_prefix'] = trim ($_POST['db_prefix']);
  $passwd = $_POST['password2'];

  if( $passwd !== $_POST['password'] )
  {
    $error_msg = "Пароли не совпадают";
    break;
  }
  
  if( ! @mysql_pconnect ($INFO['db_server'], $INFO['db_login'], $INFO['db_password']) )
  {
    $error_msg = "Не удалось подключиться к базе данных. Проверьте парметры подключения";
    break;
  }
  
  if( ! @mysql_select_db ($INFO['db_database']) )
  {
    $error_msg = "Не удалось выбрать базу данных $INFO[db_database]";
    break;
  }
  
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]chatconf");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]chatconf (
    chatblock int,
    maxmes int,
    maxtime int,
    maxlen int,
    refresh int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]chatconf</b>";
    break;
  }
  
  mysql_query ("insert into $INFO[db_prefix]chatconf
               (chatblock,maxmes,maxtime,maxlen,refresh) values
               ('1','32','60','1024','10')") or die (mysql_error ());

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]chatmes");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]chatmes (
    k_mes int primary key auto_increment,
    k_room int,
    ip varchar(15),
    user varchar(16),
    mesdate int,
    post text
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]chatmes</b>";
    break;
  }
  
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]links");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]links (
    k_link int primary key auto_increment,
    title varchar(255),
    url varchar(255),
    pos int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]links</b>";
    break;
  }
  
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]photos");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]photos (
    k_photo int primary key auto_increment,
    k_art int,
    pictur varchar(255),
    title varchar(255),
    caption varchar(255),
    pos int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]photos</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]book");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]book (
    k_post int primary key auto_increment,
    k_art int,
    ip varchar(15),
    user varchar(16),
    email varchar(40),
    url varchar(60),
    icq varchar(16),
    mesdate int,
    post mediumtext
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]book</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]news");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]news (
    k_new int primary key auto_increment,
    k_art int,
    mesdate int,
    title text,
    post mediumtext,
    faq int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]news</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]counter");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]counter (
    k_cnt int primary key auto_increment,
    ip varchar(15),
    url varchar(255),
    reftime int,
    agent varchar(255),
    cnt int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]counter</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]refer");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]refer (
    k_ref int primary key auto_increment,
    url varchar(255),
    cnt int,
    last_d int,
    ip varchar(15)
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]refer</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]find");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]find (
    k_fnd int primary key auto_increment,
    fstring varchar(255),
    fdate int,
    cnt int,
    ip varchar(15)
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]find</b>";
    break;
  }
  
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]dlcnt");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]dlcnt (
    k_dl int primary key auto_increment,
    file varchar(255),
    cnt int,
    lastdate int,
    ip varchar(15)
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]dlcnt</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]art");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]art (
    k_art int primary key auto_increment,
    mnemonic varchar(255),
    title varchar(255),
    author varchar(255),
    sup int,
    pos int,
    cont int,
    dynamic int,
    format int,
    path int,
    p_n int,
    inmenu int,
    mesdate int,
    caption varchar(255),
    post mediumtext,
    shot mediumtext,
    keywords text,
    icon varchar(255),
    words mediumtext,
    comment mediumtext,
    cnt int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]art</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]email");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]email (
    k_mail int primary key auto_increment,
    email varchar(40),
    ip varchar(15),
    cd varchar(32),
    active int,
    qtime int,
    send_time int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]email</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]spam");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]spam (
    k_sp int primary key auto_increment,
    mode int,
    ip varchar(15),
    posttime int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]spam</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]hacks");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]hacks (
    k_hck int primary key auto_increment,
    pass varchar(255),
    ip varchar(15),
    hacktime int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]hacks</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]ban");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]ban (
    k_ban int primary key auto_increment,
    ip varchar(15),
    mode int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]ban</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]badname");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]badname (
    k_bn int primary key auto_increment,
    bname varchar(16)
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]badname</b>";
    break;
  }
  
  mysql_query ("insert into $INFO[db_prefix]badname (bname) values ('admin')") or die (mysql_error ());

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]censor");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]censor (
    k_cns int primary key auto_increment,
    bad varchar(255),
    good varchar(255)
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]censor</b>";
    break;
  }
  
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]sessions");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]sessions (
    k_ses int primary key auto_increment,
    sid varchar(32),
    stime int,
    agent varchar(255),
    ip varchar(15)
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]sessions</b>";
    break;
  }
  
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]nums");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]nums (
    k_num int primary key auto_increment,
    sid varchar(255),
    num varchar(6),
    stime int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]nums</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]pools");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]pools (
    k_pl int primary key auto_increment,
    question varchar(255),
    active int,
    begdate int,
    enddate int,
    cnt int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]pools</b>";
    break;
  }

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]answers");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]answers (
    k_ans int primary key auto_increment,
    k_pl int,
    vote int,
    answer varchar(255),
    pos int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]answers</b>";
    break;
  }  
  
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]messages");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]messages (
    error_cap varchar(255),
    inform_cap varchar(255),
    error1 varchar(255),
    error2 varchar(255),
    error3 varchar(255),
    error4 varchar(255),
    error5 varchar(255),
    error6 varchar(255),
    error7 varchar(255),
    error8 varchar(255),
    error9 varchar(255),
    error10 varchar(255),
    error11 varchar(255),
    error12 varchar(255),
    error13 varchar(255),
    error14 varchar(255),
    error15 varchar(255),
    error16 varchar(255),
    error17 varchar(255),
    inform1 varchar(255),
    inform2 varchar(255),
    inform3 varchar(255),
    inform4 varchar(255),
    inform5 varchar(255),
    inform6 varchar(255),
    inform7 text
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]messages</b>";
    break;
  }

  mysql_query ("insert into $INFO[db_prefix]messages
              (error_cap,inform_cap,error1,error2,error3,error4,error5,error6,error7,error8,error9,error10,error11,error12,error13,error14,error15,
               error16,error17,inform1,inform2,inform3,inform4,inform5,inform6,inform7) values
              ('Произошла ошибка','Информация',
              'В системе комментариев работает флуд контроль.<br />Отправте сообщение позже.',
              'Вам запрещено оставлять сообщения.<br />За разъяснениями обратитесь к администратору сайта.',
              'В системе подписки на рассылку работает флуд контроль.<br />Попробуйте повторить позже.',
              'Вы забыли ввести имя.',
              'Сообщение очень короткое.',
              'Не верно записан адрес e-mail.',
              'Неверный код подтверждения подписки на рассылку.',
              'Запрашиваемый раздел не существует.',
              'Вам закрыт доступ на этот сайт.<br />За разъяснениями обратитесь к администратору сайта.',
              'Вы уже отвечали на этот опрос.',
              'Файл не найден.',
              'Имя {NAME} нельзя использовать.',
              'В данном разделе нельзя оставлять комментарии.',
              'Вы уже отправляли письмо, попробуйте повторить позже.',
              'Заполнены не все необходимые поля.',
              'Не верно введены контрольные цифры.',
              'Ошибка при отправке письма.<br />Попробуйте повторить еще раз.',
              'На адрес {MAIL} выслано контрольное письмо.<br />Действуйте в соответствии с его инструкциями.',
              'Ящик {MAIL} успешно добавлен в список рассылки.',
              'Активация ящика {MAIL} уже завершена.',
              'Ящик {MAIL} уже содержится в списке.<br />Если он не активирован, действуйте в соответствии с инструкциями контрольного письма.',
              'Ящик {MAIL} успешно удален из списка рассылки.',
              'Письмо было отправлено.<br />Администратор сайта сможет ответить Вам на указанный Вами ящик.',
              '<h5>{FILE}</h5><table><tr><th>Размер:</th><td>{SIZE}</td></tr><tr><th>Создан:</th><td>{CREATING}</td></tr><tr><th>Скачено:</th><td>{COUNT}</td></tr>{?ADMIN}<tr><th>Дата:</th><td>{DATE}</td></tr><tr><th>IP:</th><td>{IP}</td></tr>{ADMIN?}</table><form method=\"post\" action=\"download.php\"><input type=\"hidden\" name=\"file\" value=\"{FILE}\" /><input type=\"submit\" value=\"Скачать\" /></form>')
              ") or die (mysql_error ());
              
  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]blocks");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]blocks (
    banner mediumtext,
    c_title mediumtext,
    lastnew int,
    find int,
    spam int,
    b_stat int,
    mail int,
    durat int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]blocks</b>";
    break;
  }

  mysql_query ("insert into $INFO[db_prefix]blocks
               (banner,c_title,lastnew,find,spam,b_stat,mail,durat) values
               ('','',2,1,1,1,1,1)
               ") or die (mysql_error ());

  mysql_query ("DROP TABLE IF EXISTS $INFO[db_prefix]config");
  
  if( ! mysql_query ("CREATE TABLE $INFO[db_prefix]config (
    skin varchar(255),
    title varchar(255),
    datefrm varchar(255),
    email varchar(255),
    signature mediumtext,
    afrom varchar(255),
    passwrd varchar(32),
    counter int,
    viewer int,
    maxcnt int,
    maxdate int,
    hst int,
    nws int,
    mpp int,
    menuwidth varchar(16),
    url varchar(255),
    stat int,
    new int,
    sp_book int,
    sp_mail int,
    sp_send int,
    meslen int,
    new_send int,
    sketchwidth int,
    sketchheight int,
    photocols int,
    siteinstall int
    )") )
  {
    $error_msg = "Не удалось создать таблицу <b>$INFO[db_prefix]config</b>";
    break;
  }

  $pass = md5 ($passwd);
  $url = get_url (trim ($_POST['site']));

  mysql_query ("insert into $INFO[db_prefix]config
              (skin,title,datefrm,email,passwrd,signature,afrom,counter,viewer,maxcnt,maxdate,hst,nws,mpp,menuwidth,url,
               stat,sp_book,sp_mail,sp_send,new,meslen,new_send,sketchwidth,sketchheight,photocols,siteinstall) values
              ('skins/default','$_POST[title]','d.m.Y H:i','$_POST[email]','$pass',
              '<br />С уважением,<br />\nадминистрация сайта <a href=\"{SITE_URL}\" target=\"_blank\">{SITE}</a>',
              '{SITE}',0,0,0," . time() . ",10,10,10,'200px','$url',30,30,60,600,7,4096,1,96,64,4," . time() . ")
              ") or die (mysql_error ());
  
  
  $f = @fopen ("conf.php", "w");
  if ( !$f )
  {
    $error_msg = "Не удалось создать конфигурационный файл";
    break;
  }
  
  flock ($f, LOCK_EX);
  fputs ($f, "<?php\n");
  foreach ($INFO as $key => $value) fputs ($f, "\$INFO['$key'] = '$value';\n");
  fputs ($f, "?>");
  flock ($f, LOCK_UN);
  fclose ($f);
  
  $f = @fopen (".htaccess", "w");
  if ( $f )
  {
    flock ($f, LOCK_EX);
    fputs ($f, "DirectoryIndex index.php\n");
    flock ($f, LOCK_UN);
    fclose ($f);
  }
  else $war .= "<div>Не удалось создать файл <b>.htaccess</b></div>";

  $f = fopen ("install.lock", "w");
  if ( $f )
  {
    flock ($f, LOCK_EX);
    fputs ($f, "install lock\n");
    flock ($f, LOCK_UN);
    fclose ($f);
  } else $war .= "<div>Не удалось создать файл <b>install.lock</b> для блокировки инсталятора.<br />
  					Создайте его вручную в целях безопасности.<div>";
  
  if ( ! empty($war) ) $war = "<div align=center style='border:1px solid #000000;background:#FFF0A0;color:#000000;'><b>$war</b>";
  echo "<html><head><title>Установка завершена</title></head>
  		<link href='includes/admincss.css' type='text/css' rel='stylesheet' />
  		<body>
  		<center><table><tr><td align='center'>$war<h1 align='center'>Установка завершена</h1><a href='admin.php'> В Х О Д </a></td></tr></table>
  		</center></body></html>";
  exit;
}
$db_server = $db_login = $db_database = $db_prefix = $title = $site = $email = "";
if ( isset ($_POST['db_server']) ) $db_server = trim ($_POST['db_server']);
if ( isset ($_POST['db_login']) ) $db_login = trim ($_POST['db_login']);
if ( isset ($_POST['db_database']) ) $db_database = trim ($_POST['db_database']);
if ( isset ($_POST['db_prefix']) ) $db_prefix = trim ($_POST['db_prefix']);
if ( isset ($_POST['title']) ) $title = trim ($_POST['title']);
if ( isset ($_POST['site']) ) $site = trim ($_POST['site']);
if ( isset ($_POST['email']) ) $email = trim ($_POST['email']);
if ( empty ($email) ) $email = 'admin@localhost';
if ( empty ($title) ) $title = $_SERVER['SERVER_NAME'];
if ( empty ($site) ) $site = 'http://' . $_SERVER['SERVER_NAME'] . DIRNAME ($_SERVER['SCRIPT_NAME']);
if ( file_exists ("conf.php") )
{
  include ('conf.php');
  if ( empty ($db_server) ) $db_server = $INFO['db_server'];
  if ( empty ($db_database) ) $db_database = $INFO['db_database'];
  if ( empty ($db_prefix) ) $db_prefix = $INFO['db_prefix'];
  if ( empty ($db_login) ) $db_login = $INFO['db_login'];
} else
{
  if ( empty ($db_prefix) ) $db_prefix = 'cms_';
  if ( empty ($db_server) ) $db_server = $_SERVER['SERVER_NAME'];
  if ( empty ($db_database) ) $db_database = 'test';
  if ( empty ($db_login) ) $db_login = 'root';
}

if ( ! empty ($error_msg) ) $error_msg = "<div align=center style='border:1px solid #000000;background:#FFA0A0;color:#000000;'><b>$error_msg</b></div>";

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
<title>Установка системы автоматизации управления контентом сайта</title>
</head>
<link href='includes/admincss.css' type='text/css' rel='stylesheet' />
<body>
<center><table><tr><td align='center'>
<h1>Установка системы автоматизации управления контентом сайта</h1>
$error_msg
<a href='help/install.html' target='_blank'>Помощь в установке</a>
<form action='install.php' method='post'>
<input type='hidden' name='go' value='ok' />
<table>
<tr><td><b>Сервер MySQL</b></td><td><input type='text' size='30' name='db_server' value='$db_server' /></td></tr>
<tr><td><b>Логин MySQL</b></td><td><input type='text' size='30' name='db_login' value='$db_login' /></td></tr>
<tr><td><b>Пароль MySQL</b></td><td><input type='password' size='30' name='db_password' /></td></tr>
<tr><td><b>База данных</b></td><td><input type='text' size='30' name='db_database' value='$db_database' /></td></tr>
<tr><td><b>Префикс таблиц</b></td><td><input type='text' size='30' name='db_prefix' value='$db_prefix' /></td></tr>
<tr><td><b>Название сайта</b></td><td><input type='text' size='30' name='title' value='$title' maxlength='60' /></td></tr>
<tr><td><b>Адрес сайта</b></td><td><input type='text' size='30' name='site' value='$site' maxlength='60' /></td></tr>
<tr><td><b>Email администратора</b></td><td><input type='text' size='30' name='email' value='$email' maxlength='60' /></td></tr>
<tr><td><b>Пароль администратора</b></td><td><input type='password' size='30' name='password' /></td></tr>
<tr><td><b>Повторите пароль</b></td><td><input type='password' size='30' name='password2' /></td></tr>
</table>
<input type='submit' value='Установить' />
</form>
</td></tr></table></center>
</body>
</html>
";
?>
