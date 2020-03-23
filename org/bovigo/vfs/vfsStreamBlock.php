<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsBlock as Base;

class_exists('bovigo\vfs\vfsBlock');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamBlock" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsBlock" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsBlock" instead */
    class vfsStreamBlock extends Base
    {
    }
}
