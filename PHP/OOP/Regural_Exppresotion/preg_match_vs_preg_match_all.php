<?php

$text = 'att@';
$pattern = '/^[a-zA-Z0-9]{2,4}$/i';

echo preg_match($pattern, $text);

?>
<?php

$text = '+8801703441735';
$pattern = '/^(\+88)?01[3-9][0-9]{8}$/';

echo preg_match($pattern, $text);

?>