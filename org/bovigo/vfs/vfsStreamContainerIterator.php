<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamContainerIterator as Base;

class_exists('bovigo\vfs\vfsStreamContainerIterator');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContainerIterator" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamContainerIterator" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamContainerIterator" instead */
    class vfsStreamContainerIterator extends Base
    {
    }
}
