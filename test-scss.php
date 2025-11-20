<?php
require __DIR__ . '/vendor/autoload.php';

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

$scss = 'body { color: red; }';

$compiler = new Compiler();
$compiler->setOutputStyle(OutputStyle::COMPRESSED);

echo $compiler->compileString($scss)->getCss();
