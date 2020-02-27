<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsDirectory as Base;

class_exists('bovigo\vfs\vfsDirectory');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamDirectory" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsDirectory" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsDirectory" instead */
    class vfsStreamDirectory extends Base
    {
    }
}
