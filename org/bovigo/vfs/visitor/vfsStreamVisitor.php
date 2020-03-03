<?php

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\vfsStreamVisitor as Base;

interface_exists('bovigo\vfs\visitor\vfsStreamVisitor');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamVisitor" interface is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\visitor\vfsStreamVisitor" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStreamVisitor" instead */
    interface vfsStreamVisitor extends Base
    {
    }
}
