<?php
/**
 * Some utility methods for vfsStream.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamWrapper.php';
/**
 * Some utility methods for vfsStream.
 *
 * @package     bovigo_vfs
 */
class vfsStream
{
    /**
     * url scheme
     */
    const SCHEME = 'vfs';

    /**
     * prepends the scheme to the given URL
     *
     * @param   string  $path
     * @return  string
     */
    public static function url($path)
    {
        return self::SCHEME . '://' . str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    /**
     * restores the path from the url
     *
     * @param   string  $url
     * @return  string
     */
    public static function path($url)
    {
        $path = substr($url, strlen(self::SCHEME . '://'));
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        return $path;
    }

    /**
     * returns a new file with given name
     *
     * @param   string         $name
     * @return  vfsStreamFile
     */
    public static function newFile($name)
    {
        return new vfsStreamFile($name);
    }

    /**
     * returns a new directory with given name
     *
     * If the name contains slashes, a new directory structure will be created.
     * The returned directory will always be the parent directory of this
     * directory structure.
     *
     * @param   string              $name
     * @return  vfsStreamDirectory
     */
    public static function newDirectory($name)
    {
        if ('/' === $name{0}) {
            $name = substr($name, 1);
        }
        
        $firstSlash = strpos($name, '/');
        if (false === $firstSlash) {
            return new vfsStreamDirectory($name);
        }
        
        $ownName   = substr($name, 0, $firstSlash);
        $subDirs   = substr($name, $firstSlash + 1);
        $directory = new vfsStreamDirectory($ownName);
        self::newDirectory($subDirs)->at($directory);
        return $directory;
    }
}
?>