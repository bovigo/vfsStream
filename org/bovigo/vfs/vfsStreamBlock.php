<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamBlock as Base;

class_exists('bovigo\vfs\vfsStreamBlock');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamBlock" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamBlock" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamBlock" instead */
    class vfsStreamBlock extends Base
    {
    }
}
