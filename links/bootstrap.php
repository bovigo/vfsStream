<?php
require __DIR__ . '/../vendor/autoload.php';
use org\bovigo\vfs\vfsStream;

$root = vfsStream::setup();
$dir  = vfsStream::newDirectory('some')->at($root);
$file = vfsStream::newFile('target.txt')->withContent('hello, world!')->at($dir);
$link = vfsStream::newSymlink('link', $file)->at($dir);

