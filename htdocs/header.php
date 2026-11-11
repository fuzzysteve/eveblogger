<?php
header('Cache-Control: public');
header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+30 minutes')).' GMT');

