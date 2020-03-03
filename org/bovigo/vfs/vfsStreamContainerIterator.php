<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamContainerIterator as Base;

class_exists('bovigo\vfs\vfsStreamContainerIterator');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContainerIterator" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsStreamContainerIterator" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStreamContainerIterator" instead */
    class vfsStreamContainerIterator extends Base
    {
    }
}
