<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs\linking;

require 'bootstrap.php';

use org\bovigo\vfs\vfsStream;

$root = vfsStream::setup();
vfsStream::newDirectory('some')->at($root);

var_dump(linkinfo('vfs://root/some/link'));