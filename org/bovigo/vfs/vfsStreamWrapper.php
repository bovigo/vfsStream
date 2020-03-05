<?php

namespace org\bovigo\vfs;

use bovigo\vfs\StreamWrapper as Base;

class_exists('bovigo\vfs\StreamWrapper');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamWrapper" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\StreamWrapper" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\StreamWrapper" instead */
    class vfsStreamWrapper extends Base
    {
    }
}
