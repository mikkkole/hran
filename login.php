<?php
  require_once('startsession.php');
  $page_title = 'Вход в приложение';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');

  // Clear the error message
  $error_msg = "";

  // If the user isn't logged in, try to log them in
  if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['submit'])) {
      // Connect to the database
	  StartDB();

      // Grab the user-entered log-in data
      $user_login = mysqli_real_escape_string($dbc, trim($_POST['login']));
      $user_password = mysqli_real_escape_string($dbc, trim($_POST['password']));

      if (!empty($user_login) && !empty($user_password)) {
        // Look up the login and password in the database
        $query = "SELECT user_id, login FROM users WHERE login = '$user_login' AND password = SHA('$user_password')";
        $data = mysqli_query($dbc, $query);

        if (mysqli_num_rows($data) == 1) {
          // The log-in is OK so set the user ID and login session vars (and cookies), and redirect to the home page
          $row = mysqli_fetch_array($data);
          $_SESSION['user_id'] = $row['user_id'];
          $_SESSION['login'] = $row['login'];
          setcookie('user_id', $row['user_id'], time() + (60 * 60 * 24 * 30));    // expires in 30 days
          setcookie('login', $row['login'], time() + (60 * 60 * 24 * 30));  // expires in 30 days
          $home_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
          header('Location: ' . $home_url);
        }
        else {
          // The login/password are incorrect so set an error message
          $error_msg = 'Sorry, you must enter a valid login and password to log in.';
        }
      }
      else {
        // The login/password weren't entered so set an error message
        $error_msg = 'Sorry, you must enter your login and password to log in.';
      }
    }
  }

  // If the session var is empty, show any error message and the log-in form; otherwise confirm the log-in
if (empty($_SESSION['user_id'])) {
    echo '<p class="error">' . $error_msg . '</p>';
?>

  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
      <legend>Войти</legend>
      <label for="login">Логин:</label>
      <input type="text" name="login" value="<?php if (!empty($user_login)) echo $user_login; ?>" /><br />
      <label for="password">Password:</label>
      <input type="password" name="password" />
    </fieldset>
    <input type="submit" value="Log In" name="submit" />
  </form>

<?php
  }
  else {
    // Confirm the successful log-in
    echo('<p class="login">You are logged in as ' . $_SESSION['login'] . '.</p>');
  }
?>
  
<?php
require_once('footer.php');
?>
