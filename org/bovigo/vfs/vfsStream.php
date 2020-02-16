<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStream as Base;

class_exists('bovigo\vfs\vfsStream');

@trigger_error('Using the "org\bovigo\vfs\vfsStream" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStream" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStream" instead */
    class vfsStream extends Base
    {
    }
}
