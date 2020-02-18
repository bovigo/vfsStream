<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\DotDirectory as Base;

class_exists('bovigo\vfs\DotDirectory');

@trigger_error('Using the "org\bovigo\vfs\DotDirectory" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\DotDirectory" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\DotDirectory" instead */
    class DotDirectory extends Base
    {
    }
}
