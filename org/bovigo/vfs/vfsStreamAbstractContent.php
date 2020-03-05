<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamAbstractContent as Base;

class_exists('bovigo\vfs\vfsStreamAbstractContent');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamAbstractContent" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsStreamAbstractContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStreamAbstractContent" instead */
    abstract class vfsStreamAbstractContent extends Base
    {
    }
}
