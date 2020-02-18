<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamWrapper as Base;

class_exists('bovigo\vfs\vfsStreamWrapper');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamWrapper" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamWrapper" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamWrapper" instead */
    class vfsStreamWrapper extends Base
    {
    }
}
