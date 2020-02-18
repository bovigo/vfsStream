<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamDirectory as Base;

class_exists('bovigo\vfs\vfsStreamDirectory');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamDirectory" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamDirectory" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamDirectory" instead */
    class vfsStreamDirectory extends Base
    {
    }
}
