<?php
/**
 * Example class to demonstrate testing of failure behaviour with vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 * @version     $Id$
 */
/**
 * Example class to demonstrate testing of failure behaviour with vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 */
class FailureExample
{
    /**
     * filename to write data
     *
     * @var  string
     */
    protected $filename;

    /**
     * constructor
     *
     * @param  string  $id
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * sets the directory
     *
     * @param  string  $directory
     */
    public function writeData($data)
    {
        $bytes = @file_put_contents($this->filename, $data);
        if (false === $bytes) {
            return 'could not write data';
        }
        
        return 'ok';
    }

    // more source code here...
}
?>