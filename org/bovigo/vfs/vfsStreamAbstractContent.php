<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamAbstractContent as Base;

class_exists('bovigo\vfs\vfsStreamAbstractContent');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamAbstractContent" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamAbstractContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamAbstractContent" instead */
    abstract class vfsStreamAbstractContent extends Base
    {
    }
}
