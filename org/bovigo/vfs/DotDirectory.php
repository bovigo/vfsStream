<?php

namespace org\bovigo\vfs;

use bovigo\vfs\DotDirectory as Base;

class_exists('bovigo\vfs\DotDirectory');

@trigger_error('Using the "org\bovigo\vfs\DotDirectory" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\DotDirectory" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\DotDirectory" instead */
    class DotDirectory extends Base
    {
    }
}
