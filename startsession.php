<?php
  session_start();

  // If the session vars aren't set, try to set them with a cookie
  if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['login'])) {
      $_SESSION['user_id'] = $_COOKIE['user_id'];
      $_SESSION['login'] = $_COOKIE['login'];
    }
  }