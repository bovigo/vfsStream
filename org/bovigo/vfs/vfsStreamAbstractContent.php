<?php

namespace org\bovigo\vfs;

use bovigo\vfs\BasicFile as Base;

class_exists('bovigo\vfs\BasicFile');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamAbstractContent" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\BasicFile" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\BasicFile" instead */
    abstract class vfsStreamAbstractContent extends Base
    {
    }
}
