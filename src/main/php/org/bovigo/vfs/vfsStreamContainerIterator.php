<?php
/**
 * Iterator for children of a directory container.
 *
 * @package  bovigo_vfs
 */
/**
 * Iterator for children of a directory container.
 *
 * @package  bovigo_vfs
 */
class vfsStreamContainerIterator implements Iterator
{
    /**
     * list of children from container to iterate over
     *
     * @var  array<vfsStreamContent>
     */
    protected $children = array();

    /**
     * constructor
     *
     * @param  array<vfsStreamContent>  $children
     */
    public function __construct(array $children)
    {
        $this->children = $children;
        reset($this->children);
    }

    /**
     * resets children pointer
     */
    public function rewind()
    {
        reset($this->children);
    }

    /**
     * returns the current child
     *
     * @return  vfsStreamContent
     */
    public function current()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }
        
        return $child;
    }

    /**
     * returns the name of the current child
     *
     * @return  string
     */
    public function key()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }
        
        return $child->getName();
    }

    /**
     * iterates to next child
     */
    public function next()
    {
        next($this->children);
    }

    /**
     * checks if the current value is valid
     *
     * @return  bool
     */
    public function valid()
    {
        return (false !== current($this->children));
    }
}
?>