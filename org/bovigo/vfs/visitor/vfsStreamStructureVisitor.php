<?php

declare(strict_types=1);

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\vfsStreamStructureVisitor as Base;

class_exists('bovigo\vfs\visitor\vfsStreamStructureVisitor');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamStructureVisitor" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\visitor\vfsStreamStructureVisitor" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamStructureVisitor" instead */
    class vfsStreamStructureVisitor extends Base
    {
    }
}
