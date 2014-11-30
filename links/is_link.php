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

var_dump(is_link($link->url()));

var_dump(is_link($file->url()));
