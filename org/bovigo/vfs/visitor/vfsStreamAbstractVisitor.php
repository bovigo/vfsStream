<?php

declare(strict_types=1);

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\vfsStreamAbstractVisitor as Base;

class_exists('bovigo\vfs\visitor\vfsStreamAbstractVisitor');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamAbstractVisitor" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\visitor\vfsStreamAbstractVisitor" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamAbstractVisitor" instead */
    abstract class vfsStreamAbstractVisitor extends Base
    {
    }
}
