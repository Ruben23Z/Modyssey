<?php

// Fix schema
$schema = file_get_contents('modyssey_schema (1).sql');
$schema = preg_replace('/\bTABLE mod\b/', 'TABLE `mod`', $schema);
file_put_contents('modyssey_schema (1).sql', $schema);

// Fix Mod.php
$modPhp = file_get_contents('models/Mod.php');
$modPhp = str_replace('FROM mod m', 'FROM `mod` m', $modPhp);
$modPhp = str_replace('INTO mod', 'INTO `mod`', $modPhp);
$modPhp = str_replace('UPDATE mod SET', 'UPDATE `mod` SET', $modPhp);
$modPhp = str_replace('DELETE FROM mod ', 'DELETE FROM `mod` ', $modPhp);
file_put_contents('models/Mod.php', $modPhp);

echo "Fixed reserved keyword\n";
