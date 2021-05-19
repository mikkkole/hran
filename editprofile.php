<?php
  require_once('startsession.php');
  $page_title = 'Редактирование профиля';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');

  // Убеждаемся, что пользователь залогинен.
  if (!isset($_SESSION['user_id'])) {
    echo '<p class="login">Пожалуйста, <a href="login.php">войдите, </a>чтобы получить доступ к этой странице.</p>';
    exit();
  }
  else {
    echo('<p class="login">Вы вошли как ' . $_SESSION['login'] . '. <a href="logout.php">Выйти</a>.</p>');
  }

StartDB();

  if (isset($_POST['submit'])) {
    // Получаем данные из POST
    $login = mysqli_real_escape_string($dbc, trim($_POST['login']));
    $username = mysqli_real_escape_string($dbc, trim($_POST['username']));
    $usersurname = mysqli_real_escape_string($dbc, trim($_POST['usersurname']));
    $user_email = mysqli_real_escape_string($dbc, trim($_POST['user_email']));
    $user_phone = mysqli_real_escape_string($dbc, trim($_POST['user_phone']));
    $user_phone_for_db = preg_replace('/[\+\(\)\-\.\s]/', '', $user_phone);
    $old_password = mysqli_real_escape_string($dbc, trim($_POST['old_password']));
    $new_password1 = mysqli_real_escape_string($dbc, trim($_POST['new_password1']));
    $new_password2 = mysqli_real_escape_string($dbc, trim($_POST['new_password2']));
    $old_picture = mysqli_real_escape_string($dbc, trim($_POST['old_picture']));
    $error = false;
    $new_picture = mysqli_real_escape_string($dbc, trim($_FILES['new_picture']['name']));
    $phone_maket = '/^\\d{11}$/'; // 79111111111
        
    // Проверяем новую картинку
    if (!empty($new_picture)) {

	list($new_picture_width, $new_picture_height) = getimagesize($_FILES['new_picture']['tmp_name']);
    $new_picture_type = $_FILES['new_picture']['type'];
    $new_picture_size = $_FILES['new_picture']['size']; 

      if ((($new_picture_type == 'image/gif') || ($new_picture_type == 'image/jpeg') || ($new_picture_type == 'image/pjpeg') ||
        ($new_picture_type == 'image/png')) && ($new_picture_size > 0) && ($new_picture_size <= HR_MAXFILESIZE) &&
        ($new_picture_width <= HR_MAXIMGWIDTH) && ($new_picture_height <= HR_MAXIMGHEIGHT)) {
        if ($_FILES['new_picture']['error'] == 0) {
          // Move the file to the target upload folder
          $target = HR_UPLOADPATH . basename($new_picture);
          if (move_uploaded_file($_FILES['new_picture']['tmp_name'], $target)) {
            // The new picture file move was successful, now make sure any old picture is deleted
            if (!empty($old_picture) && ($old_picture != $new_picture)) {
              @unlink(HR_UPLOADPATH . $old_picture);
            }
          }
          else {
            // Картинка не прошла загрузку, удаляем файл и ставим флаг ошибки
            @unlink($_FILES['new_picture']['tmp_name']);
            $error = true;
            echo '<p class="error">С загрузкой фото проблемы...</p>';
          }
        }
      }
      else {
        // Картинка не прошла проверку на тип файла, удаляем файл и ставим флаг ошибки
        @unlink($_FILES['new_picture']['tmp_name']);
        $error = true;
        echo '<p class="error">Фото должно быть GIF, JPEG, или PNG не больше ' . (HR_MAXFILESIZE / 1024) .
          ' KB и ' . HR_MAXIMGWIDTH . 'x' . HR_MAXIMGHEIGHT . ' пикселей.</p>';
      }
    } // конец проверки новой картинки

    // проверка номер телефона
    if (!preg_match($phone_maket, $user_phone_for_db)) { // номер телефона не совпадает
      echo '<p class="error">Вы ввели неправильный номер телефона</p>';
      $error = true;
    }

    // проверка e-mail
    if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\._\-&!?=#]*@/', $user_email)) {
      echo '<p class="error">У Вас неверный адрес электронной почты.</p>';
      $error = true;
    }
    else {
      $domain = preg_replace('/^[a-zA-Z0-9][a-zA-Z0-9\._\-&!?=#]*@/', '', $user_email);
      if (!checkdnsrr($domain)) {
        echo '<p class="error">У Вашей электронной почты несуществующий домен.</p>';
        $error = true;
      }
    }

    // Обновляем данные профиля
    if (!$error) {
      if (!empty($login) && !empty($old_password) && !empty($new_password1) && !empty($new_password2) && ($new_password1 == $new_password2)) {
        // Если есть новая картинка
        if (!empty($new_picture)) {
          $query = "UPDATE users SET login = '$login', username = '$username', usersurname = '$usersurname', user_email = '$user_email', user_phone = '$user_phone_for_db'," .
            " password = SHA('$new_password1'), user_avatar = '$new_picture' WHERE user_id = '" . $_SESSION['user_id'] . "' AND password = SHA('$old_password')";

        }
        else {
          $query = "UPDATE users SET login = '$login', username = '$username', usersurname = '$usersurname', user_email = '$user_email', user_phone = '$user_phone_for_db'," .
            " password = SHA('$new_password1') WHERE user_id = '" . $_SESSION['user_id'] . "' AND password = SHA('$old_password')";
        }
       
        mysqli_query($dbc, $query);

        // Подтверждение внесения данных
        echo '<p>Информация о профиле обновлена: </p>';
		$query = "SELECT login, username, usersurname, user_email, user_phone, user_avatar FROM users WHERE user_id = '" . $_SESSION['user_id'] . "'";
		$data = mysqli_query($dbc, $query);
		$row = mysqli_fetch_array($data);

		if ($row != NULL) {
		  echo 'Логин: ' . $row['login'] . '<br>';
		  echo 'Имя: ' . $row['username'] . '<br>';
		  echo 'Фамилия: ' . $row['usersurname'] . '<br>';
		  echo 'E-mail: ' . $row['user_email'] . '<br>';
      echo 'Телефон: +' . $row['user_phone'] . '<br>';
		  echo '<img class="cover circle" src="' . HR_UPLOADPATH . $row['user_avatar'] . '" alt="avatar" />';
		}
		else {
		  echo '<p class="error">Ошибка доступа к Вашему профилю.</p>';
		}

        echo '<p>Хотите вернуться <a href="index.php">на главную страницу</a>?</p>';
        
        mysqli_close($dbc);
        exit();
      }
      else {
        echo '<p class="error">Нам нужны все Ваши данные! (можно без фото).</p>';
      }
    }
  } // Конец проверки submit
  else {
    // Загружаем данные из БД
    $query = "SELECT login, username, usersurname, user_email, user_phone, user_avatar FROM users WHERE user_id = '" . $_SESSION['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    $row = mysqli_fetch_array($data);

    if ($row != NULL) {
      $login = $row['login'];
      $username = $row['username'];
      $usersurname = $row['usersurname'];
      $user_email = $row['user_email'];
      $user_phone_for_db = $row['user_phone'];
      $old_picture = $row['user_avatar'];
    }
    else {
      echo '<p class="error">Ошибка доступа к Вашему профилю.</p>';
    }
  }

  mysqli_close($dbc);
?>

  <form enctype="multipart/form-data" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo HR_MAXFILESIZE; ?>" />
    <fieldset>
      <legend>Личная информация</legend>
      <label for="login">Логин:</label>
      <input type="text" id="login" name="login" value="<?php if (!empty($login)) echo $login; ?>" /><br />
      <label for="username">Имя:</label>
      <input type="text" id="username" name="username" value="<?php if (!empty($username)) echo $username; ?>" /><br />
      <label for="usersurname">Фамилия:</label>
      <input type="text" id="usersurname" name="usersurname" value="<?php if (!empty($usersurname)) echo $usersurname; ?>" /><br />
      <label for="user_email">E-mail:</label>
      <input type="email" id="user_email" name="user_email" value="<?php if (!empty($user_email)) echo $user_email; ?>" /><br />
      <label for="user_phone">Телефон:</label>
      <input type="user_phone" id="user_phone" name="user_phone" value="<?php if (!empty($user_phone_for_db)) echo '+' . $user_phone_for_db; ?>" /><br />
      
      <label for="old_password">Старый пароль:</label>
      <input type="password" id="old_password" name="old_password" /><br />
      <label for="new_password1">Новый пароль:</label>
      <input type="password" id="new_password1" name="new_password1" /><br />
      <label for="new_password2">Повторите пароль:</label>
      <input type="password" id="new_password2" name="new_password2" /><br />
      
      <input type="hidden" name="old_picture" value="<?php if (!empty($old_picture)) echo $old_picture; ?>" />
      <label for="new_picture">Picture:</label>
      <input type="file" id="new_picture" name="new_picture" />
      <?php if (!empty($old_picture)) {
        echo '<img class="cover circle" src="' . HR_UPLOADPATH . $old_picture . '" alt="Profile Picture" />';
      } ?>
    </fieldset>
    <input type="submit" value="Save Profile" name="submit" />
  </form>
  
<?php
require_once('footer.php');