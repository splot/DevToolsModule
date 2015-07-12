<?php
$success = opcache_reset();
$message = $success ? 'OK' : 'Failed';
die($message);
