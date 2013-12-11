# Anonymous Santa Claus #

---

## About ##

This script defines who gives whom gifts for the holiday’s and automatically sends e-mail notifications.

## Requirements ##

- PHP 5.3.0 or higher
- PECL json 1.2.0 or higher

## Get started ##

1) Add people in the file input.php, it will require the name and email.

```php
<?php
    return array(
        array(
            'name' => 'Name 1',
            'email' => 'mail1@mail.ru'
        ),
        ...
    );
?>
```

2) Change the config variables in the index.php file.

- `mailStack` - Stack of messages
- `mailSleep` - Delay before sending next stack of messages
- `mailFrom` - Email default sender
- `mailSubject` - Subject of the message
- `mailMessage` - Message, using variable #WHO# to substitute the name of the email recipient and variable #WHOM# to specify the name of the gift recipient

3) Run the script index.php from the console or through a web server.

## Troubleshooting ##

In operation, the script stores the data in a temporary file output.php. In case of a restart the script will continue the newsletter.

## Testing ##

If you specify one person in the file input.php, then he and only he receives a letter.

## Credits ##

Copyright (c) 2013 [NIMAX](http://www.nimax.ru/).
Programmed by Vladimir Parshukov.
Released under the [GNU Public License](http://opensource.org/licenses/gpl-license.php).



