<?php

include('../phpEncode.class.php');

$originalcode = '<?php
echo "hi";
return 555;
?>';

$ENC = new phpEncode();
$ENC -> makeinc = false; //copy the inc
$ENC -> fixedkey();

$ENC -> curdomain = 'phpclib';
$ENC -> makeinc = true; //new inc 
$ENC -> folder2folder('from/', 'to/');
