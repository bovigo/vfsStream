<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsDirectory as Base;

class_exists('bovigo\vfs\vfsDirectory');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamDirectory" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsDirectory" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsDirectory" instead */
    class vfsStreamDirectory extends Base
    {
    }
}
