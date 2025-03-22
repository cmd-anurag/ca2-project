<?php
if (!isset($_SESSION['user'])) {
    echo '<script src="https://unpkg.com/@tailwindcss/browser@4"></script>';
    echo "<h1 class='text-center text-2xl p-10'>You are not logged in. You will be redirected to the login page in a few seconds.</h1>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'login.html';
            }, 4000);
          </script>";
    exit();
}
