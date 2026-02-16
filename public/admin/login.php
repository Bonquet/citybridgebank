<?php
// This file exists solely to handle misrouted requests when users try to
// access the admin login page under the public directory. The real admin
// panel lives at /admin/login.php at the root of the project. Redirect
// accordingly to prevent 404 or server errors.

header('Location: ../../admin/login.php');
exit();