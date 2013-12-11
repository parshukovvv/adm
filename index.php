<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

ini_set('max_execution_time', 30); // Change if you need

require 'adm.php';

$config = array(
    // Stack of messages
    'mailStack' => 50,

    // Delay before sending next stack of messages
    'mailSleep' => 5,

    // Email default sender
    'mailFrom' => 'robot@nimax.ru',

    // Subject of the message
    'mailSubject' => 'Anonymous Santa Claus',

    // Message
    'mailMessage' => "
Hi, #WHO#\r\n
This year, you are giving a gift: #WHOM#\r\n
Your anonymous Santa Claus!\r\n"
);

\Nimax\ADM::execute($config);