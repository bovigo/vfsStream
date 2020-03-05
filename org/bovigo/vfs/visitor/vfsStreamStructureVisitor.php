<?php

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\StructureInspector as Base;

class_exists('bovigo\vfs\visitor\StructureInspector');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamStructureVisitor" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\visitor\StructureInspector" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\StructureInspector" instead */
    class vfsStreamStructureVisitor extends Base
    {
    }
}
