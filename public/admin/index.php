<?php
// This file redirects any access to /public/admin/ to the proper admin dashboard.
// The admin area does not reside under the public folder. Redirect to the
// project-level admin index.

header('Location: ../../admin/index.php');
exit();