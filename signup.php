<?php
  require_once('startsession.php');
  $page_title = 'Регистрация пользователя';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');

	StartDB();

  if (isset($_POST['submit'])) {
    // Берем данные из POST
    $login = mysqli_real_escape_string($dbc, trim($_POST['login']));
    $password1 = mysqli_real_escape_string($dbc, trim($_POST['password1']));
    $password2 = mysqli_real_escape_string($dbc, trim($_POST['password2']));
    $user_pass_phrase = sha1($_POST['verify']);

    if ($_SESSION['pass_phrase'] == $user_pass_phrase) {
      if (!empty($login) && !empty($password1) && !empty($password2) && ($password1 == $password2)) {
        // Проверка, есть ли такой логин
        $query = "SELECT * FROM users WHERE login = '$login'";
        $data = mysqli_query($dbc, $query);
        if (mysqli_num_rows($data) == 0) {
          $query = "INSERT INTO users (login, password, access, user_reg_time) VALUES ('$login', sha1('$password1'), 1, NOW())";
          mysqli_query($dbc, $query);

          echo '<p>Профиль создан. Теперь Вы можете <a href="login.php">Войти</a>.</p>';

          mysqli_close($dbc);
          exit();
        }
        else {
          // Такой логин уже есть
          echo '<p class="error">Такой логин уже есть. Придумайте другой.</p>';
          $login = "";
        }
      }
      else {
        echo '<p class="error">Вы должны заполнить все поля, в том числе пароль - дважды!</p>';
      }
    }
    else {
      echo '<p class="error">Введите верную фразу для проверки!</p>';
    }
  }

  mysqli_close($dbc);
?>

  <p>Введите логин и пароль для регистрации в Хранилище</p>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
      <legend>Регистрация</legend>
      <label for="login">Логин:</label>
      <input type="text" id="login" name="login" placeholder="<?php if (!empty($login)) echo $login; ?>" /><br />
      <label for="password1">Пароль:</label>
      <input type="password" id="password1" name="password1" placeholder="Введите пароль" /><br />
      <label for="password2">Повторите пароль:</label>
      <input type="password" id="password2" name="password2" placeholder="Повторите пароль" /><br />
      <label for="verify">Проверка:</label>
      <input type="text" id="verify" name="verify" placeholder="Введите фразу с картинки:" />
	    <img src="captcha.php" alt="Проверка идентификационной фразы" />
    </fieldset>
    <input type="submit" value="Зарегистрироваться" name="submit" />
  </form>
  
<?php
require_once('footer.php');