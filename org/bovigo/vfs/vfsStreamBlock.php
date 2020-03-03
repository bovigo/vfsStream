<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamBlock as Base;

class_exists('bovigo\vfs\vfsStreamBlock');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamBlock" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsStreamBlock" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStreamBlock" instead */
    class vfsStreamBlock extends Base
    {
    }
}
