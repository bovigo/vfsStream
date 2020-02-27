<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsDirectoryIterator as Base;

class_exists('bovigo\vfs\vfsDirectoryIterator');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContainerIterator" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsDirectoryIterator" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsDirectoryIterator" instead */
    class vfsStreamContainerIterator extends Base
    {
    }
}
