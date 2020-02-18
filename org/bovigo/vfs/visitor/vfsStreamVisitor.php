<?php

declare(strict_types=1);

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\vfsStreamVisitor as Base;

interface_exists('bovigo\vfs\visitor\vfsStreamVisitor');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamVisitor" interface is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\visitor\vfsStreamVisitor" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamVisitor" instead */
    interface vfsStreamVisitor extends Base
    {
    }
}
