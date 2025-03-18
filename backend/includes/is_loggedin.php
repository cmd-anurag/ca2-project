<?php
if (!isset($_SESSION['user'])) {
    echo "<p class='text-center text-2xl'>You are not logged in. You will be redirected to the login page in a few seconds.</p>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'login.html';
            }, 4000);
          </script>";
    exit();
}
