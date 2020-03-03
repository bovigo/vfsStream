<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStream as Base;

class_exists('bovigo\vfs\vfsStream');

@trigger_error('Using the "org\bovigo\vfs\vfsStream" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsStream" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStream" instead */
    class vfsStream extends Base
    {
    }
}
