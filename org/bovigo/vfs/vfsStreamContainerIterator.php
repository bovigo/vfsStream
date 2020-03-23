<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsDirectoryIterator as Base;

class_exists('bovigo\vfs\vfsDirectoryIterator');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContainerIterator" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsDirectoryIterator" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsDirectoryIterator" instead */
    class vfsStreamContainerIterator extends Base
    {
    }
}
