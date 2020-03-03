<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs;

use Exception;
use function class_alias;

/**
 * Exception for vfsStream errors.
 *
 * @api
 */
class vfsStreamException extends \Exception
{
    // intentionally empty
}

class_alias('bovigo\vfs\vfsStreamException', 'org\bovigo\vfs\vfsStreamException');
