<?php
  require_once('startsession.php');
  $page_title = 'Регистрация пользователя';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');

  // Connect to the database
	StartDB();

  if (isset($_POST['submit'])) {
    // Grab the profile data from the POST
    $login = mysqli_real_escape_string($dbc, trim($_POST['login']));
    $password1 = mysqli_real_escape_string($dbc, trim($_POST['password1']));
    $password2 = mysqli_real_escape_string($dbc, trim($_POST['password2']));

    if (!empty($login) && !empty($password1) && !empty($password2) && ($password1 == $password2)) {
      // Make sure someone isn't already registered using this login
      $query = "SELECT * FROM users WHERE login = '$login'";
      $data = mysqli_query($dbc, $query);
      if (mysqli_num_rows($data) == 0) {
        // The login is unique, so insert the data into the database
        $query = "INSERT INTO users (login, password, access, user_reg_time) VALUES ('$login', SHA('$password1'), 1, NOW())";
        mysqli_query($dbc, $query);

        // Confirm success with the user
        echo '<p>Your new account has been successfully created. You\'re now ready to <a href="login.php">log in</a>.</p>';

        mysqli_close($dbc);
        exit();
      }
      else {
        // An account already exists for this login, so display an error message
        echo '<p class="error">An account already exists for this login. Please use a different address.</p>';
        $login = "";
      }
    }
    else {
      echo '<p class="error">You must enter all of the sign-up data, including the desired password twice.</p>';
    }
  }

  mysqli_close($dbc);
?>

  <p>Введите логин и пароль для регистрации в Хранилище</p>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
      <legend>Регистрация</legend>
      <label for="login">Логин:</label>
      <input type="text" id="login" name="login" value="<?php if (!empty($login)) echo $login; ?>" /><br />
      <label for="password1">Пароль:</label>
      <input type="password" id="password1" name="password1" /><br />
      <label for="password2">Повторите пароль:</label>
      <input type="password" id="password2" name="password2" /><br />
    </fieldset>
    <input type="submit" value="Sign Up" name="submit" />
  </form>
  
<?php
require_once('footer.php');
?>
