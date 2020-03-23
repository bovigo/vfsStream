<?php

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\BaseVisitor;

class_exists('bovigo\vfs\visitor\BaseVisitor');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamAbstractVisitor" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\visitor\BaseVisitor" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\BaseVisitor" instead */
    abstract class vfsStreamAbstractVisitor extends BaseVisitor
    {
    }
}
