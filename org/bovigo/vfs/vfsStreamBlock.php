<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsBlock as Base;

class_exists('bovigo\vfs\vfsBlock');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamBlock" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsBlock" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsBlock" instead */
    class vfsStreamBlock extends Base
    {
    }
}
