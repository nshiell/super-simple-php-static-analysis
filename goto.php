<?php

// Needs to autoload the files, not great for a static analyser
require_once __DIR__ . '/../../vendor/autoload.php';

//$method = 'someMethod';
//$classNameShort = 'Someclass';
//$source = '/var/www/SomePath/SomeClass.php';

$source = $argv[1];
// simple path matting for Containers Vagrant etc
$source = str_replace('/home/nicholas/some-project/', '/var/www/', $source);
$methodClass = $argv[2];

if (strpos($methodClass, '::') !== false) {
    $methodParts = explode('::', $methodClass);
    if ($methodParts[0]) {
        $classNameShort = $methodParts[0];
        $method = $methodParts[1];
    } else {
        $classNameShort = $methodParts[1];
        $method = $methodParts[2] ?? '__construct';
    }
} else {
    $classNameShort = $methodClass;
    $method = '__construct';
}

$opened = file_get_contents($source);
$lines = explode(PHP_EOL, $opened);

$uses = '';
$namepace = '';
foreach ($lines as $line) {
    if (substr($line, 0, 4) == 'use ') {
        $uses.= $line . PHP_EOL;
    } elseif (substr($line, 0, 10) == 'namespace ') {
        $namepace = $line . PHP_EOL;
    }
}

$code = '<?php '
    . $namepace
    . $uses
    . sprintf('; return %s::class;', $classNameShort);

file_put_contents('/tmp/php-1.php', $code);
$className = include '/tmp/php-1.php';
unlink('/tmp/php-1.php');

$method = new \ReflectionMethod($className, $method);

$isInternal = $method->isInternal();
$meta = [
    'isInternal' => $isInternal,
    'class'      => $method->class,
    'name'       => $method->name,
];

if (!$isInternal) {
    $meta = array_merge($meta, [
        'fileName'   => $method->getFileName(),
        'startLine'  => $method->getStartLine(),
        'docComment' => PHP_EOL . $method->getDocComment(),
    ]);
}

foreach ($meta as $key => $value) {
    $spaces = str_repeat(' ', 30 - strlen($key));
    echo $key . $spaces . $value . PHP_EOL;
}
