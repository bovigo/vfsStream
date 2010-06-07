<?php
/**
 * Example showing correct file permission support introduced with 0.7.0.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 * @version     $Id$
 */
/**
 * Example showing correct file permission support introduced with 0.7.0.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 */
class FilePermissionsExample
{
    /**
     * reads configuration from given config file
     *
     * @param  mixed   $config
     * @param  string  $configFile
     */
    public function writeConfig($config, $configFile)
    {
        file_put_contents($configFile, serialize($config));
    }

    // more methods here
}
?>