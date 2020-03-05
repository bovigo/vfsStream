<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs;

use bovigo\vfs\content\FileContent;
use bovigo\vfs\content\LargeFileContent;
use bovigo\vfs\visitor\vfsStreamVisitor;
use DirectoryIterator;
use InvalidArgumentException;
use function array_map;
use function class_alias;
use function explode;
use function file_get_contents;
use function filetype;
use function function_exists;
use function implode;
use function is_array;
use function is_string;
use function octdec;
use function posix_getgid;
use function posix_getuid;
use function preg_match;
use function rawurldecode;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;

/**
 * Some utility methods for vfsStream.
 *
 * @api
 */
class vfsStream
{
    /**
     * url scheme
     */
    const SCHEME            = 'vfs';
    /**
     * owner: root
     */
    const OWNER_ROOT        = 0;
    /**
     * owner: user 1
     */
    const OWNER_USER_1       = 1;
    /**
     * owner: user 2
     */
    const OWNER_USER_2       = 2;
    /**
     * group: root
     */
    const GROUP_ROOT         = 0;
    /**
     * group: user 1
     */
    const GROUP_USER_1       = 1;
    /**
     * group: user 2
     */
    const GROUP_USER_2       = 2;
    /**
     * initial umask setting
     *
     * @type  int
     */
    protected static $umask  = 0000;
    /**
     * switch whether dotfiles are enabled in directory listings
     *
     * @type  bool
     */
    private static $dotFiles = true;

    /**
     * prepends the scheme to the given URL
     *
     * @param   string  $path  path to translate to vfsStream url
     * @return  string
     */
    public static function url($path)
    {
        return self::SCHEME . '://' . join(
                '/',
                array_map(
                        'rawurlencode',    // ensure singe path parts are correctly urlencoded
                        explode(
                                '/',
                                str_replace('\\', '/', $path)  // ensure correct directory separator
                        )
                )
        );
    }

    /**
     * restores the path from the url
     *
     * @param   string  $url  vfsStream url to translate into path
     * @return  string
     */
    public static function path($url)
    {
        // remove line feeds and trailing whitespaces and path separators
        $path = trim($url, " \t\r\n\0\x0B/\\");
        $path = substr($path, strlen(self::SCHEME . '://'));
        $path = str_replace('\\', '/', $path);
        // replace double slashes with single slashes
        $path = str_replace('//', '/', $path);
        return rawurldecode($path);
    }

    /**
     * sets new umask setting and returns previous umask setting
     *
     * If no value is given only the current umask setting is returned.
     *
     * @param   int  $umask  new umask setting
     * @return  int
     * @since   0.8.0
     */
    public static function umask($umask = null)
    {
        $oldUmask = self::$umask;
        if (null !== $umask) {
            self::$umask = $umask;
        }

        return $oldUmask;
    }

    /**
     * helper method for setting up vfsStream in unit tests
     *
     * Instead of
     * StreamWrapper::register();
     * StreamWrapper::setRoot(vfsStream::newDirectory('root'));
     * you can simply do
     * vfsStream::setup()
     * which yields the same result. Additionally, the method returns the
     * freshly created root directory which you can use to make further
     * adjustments to it.
     *
     * Assumed $structure contains an array like this:
     * <code>
     * array('Core' = array('AbstractFactory' => array('test.php'    => 'some text content',
     *                                                 'other.php'   => 'Some more text content',
     *                                                 'Invalid.csv' => 'Something else',
     *                                           ),
     *                      'AnEmptyFolder'   => array(),
     *                      'badlocation.php' => 'some bad content',
     *                )
     * )
     * </code>
     * the resulting directory tree will look like this:
     * <pre>
     * root
     * \- Core
     *  |- badlocation.php
     *  |- AbstractFactory
     *  | |- test.php
     *  | |- other.php
     *  | \- Invalid.csv
     *  \- AnEmptyFolder
     * </pre>
     * Arrays will become directories with their key as directory name, and
     * strings becomes files with their key as file name and their value as file
     * content.
     *
     * @param   string  $rootDirName  name of root directory
     * @param   int     $permissions  file permissions of root directory
     * @param   array   $structure    directory structure to add under root directory
     * @return  vfsDirectory
     * @since   0.7.0
     * @see     https://github.com/mikey179/vfsStream/issues/14
     * @see     https://github.com/mikey179/vfsStream/issues/20
     */
    public static function setup($rootDirName = 'root', $permissions = null, array $structure = array())
    {
        StreamWrapper::register();
        return self::create($structure, StreamWrapper::setRoot(self::newDirectory($rootDirName, $permissions)));
    }

    /**
     * creates vfsStream directory structure from an array and adds it to given base dir
     *
     * Assumed $structure contains an array like this:
     * <code>
     * array('Core' = array('AbstractFactory' => array('test.php'    => 'some text content',
     *                                                 'other.php'   => 'Some more text content',
     *                                                 'Invalid.csv' => 'Something else',
     *                                           ),
     *                      'AnEmptyFolder'   => array(),
     *                      'badlocation.php' => 'some bad content',
     *                )
     * )
     * </code>
     * the resulting directory tree will look like this:
     * <pre>
     * baseDir
     * \- Core
     *  |- badlocation.php
     *  |- AbstractFactory
     *  | |- test.php
     *  | |- other.php
     *  | \- Invalid.csv
     *  \- AnEmptyFolder
     * </pre>
     * Arrays will become directories with their key as directory name, and
     * strings becomes files with their key as file name and their value as file
     * content.
     *
     * If no baseDir is given it will try to add the structure to the existing
     * root directory without replacing existing childs except those with equal
     * names.
     *
     * @param   array         $structure  directory structure to add under root directory
     * @param   vfsDirectory  $baseDir    base directory to add structure to
     * @return  vfsDirectory
     * @throws  \InvalidArgumentException
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/14
     * @see     https://github.com/mikey179/vfsStream/issues/20
     */
    public static function create(array $structure, vfsDirectory $baseDir = null)
    {
        if (null === $baseDir) {
            $baseDir = StreamWrapper::getRoot();
        }

        if (null === $baseDir) {
            throw new \InvalidArgumentException('No baseDir given and no root directory set.');
        }

        return self::addStructure($structure, $baseDir);
    }

    /**
     * helper method to create subdirectories recursively
     *
     * @param   array         $structure  subdirectory structure to add
     * @param   vfsDirectory  $baseDir    directory to add the structure to
     * @return  vfsDirectory
     */
    protected static function addStructure(array $structure, vfsDirectory $baseDir)
    {
        foreach ($structure as $name => $data) {
            $name = (string) $name;
            if (is_array($data) === true) {
                self::addStructure($data, self::newDirectory($name)->at($baseDir));
            } elseif (is_string($data) === true) {
                $matches = null;
                preg_match('/^\[(.*)\]$/', $name, $matches);
                if ($matches !== array()) {
                    self::newBlock($matches[1])->withContent($data)->at($baseDir);
                } else {
                    self::newFile($name)->withContent($data)->at($baseDir);
                }
            } elseif ($data instanceof FileContent) {
                self::newFile($name)->withContent($data)->at($baseDir);
            } elseif ($data instanceof vfsFile) {
                $baseDir->addChild($data);
            }
        }

        return $baseDir;
    }

    /**
     * copies the file system structure from given path into the base dir
     *
     * If no baseDir is given it will try to add the structure to the existing
     * root directory without replacing existing childs except those with equal
     * names.
     * File permissions are copied as well.
     * Please note that file contents will only be copied if their file size
     * does not exceed the given $maxFileSize which defaults to 1024 KB. In case
     * the file is larger file content will be mocked, see
     * https://github.com/mikey179/vfsStream/wiki/MockingLargeFiles.
     *
     * @param   string        $path         path to copy the structure from
     * @param   vfsDirectory  $baseDir      directory to add the structure to
     * @param   int           $maxFileSize  maximum file size of files to copy content from
     * @return  vfsDirectory
     * @throws  \InvalidArgumentException
     * @since   0.11.0
     * @see     https://github.com/mikey179/vfsStream/issues/4
     */
    public static function copyFromFileSystem($path, vfsDirectory $baseDir = null, $maxFileSize = 1048576)
    {
        if (null === $baseDir) {
            $baseDir = StreamWrapper::getRoot();
        }

        if (null === $baseDir) {
            throw new \InvalidArgumentException('No baseDir given and no root directory set.');
        }

        $dir = new \DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            switch (filetype($fileinfo->getPathname())) {
                case 'file':
                    if ($fileinfo->getSize() <= $maxFileSize) {
                        $content = file_get_contents($fileinfo->getPathname());
                    } else {
                        $content = new LargeFileContent($fileinfo->getSize());
                    }

                    self::newFile(
                            $fileinfo->getFilename(),
                            octdec(substr(sprintf('%o', $fileinfo->getPerms()), -4))
                        )
                        ->withContent($content)
                        ->at($baseDir);
                    break;

                case 'dir':
                    if (!$fileinfo->isDot()) {
                        self::copyFromFileSystem(
                                $fileinfo->getPathname(),
                                self::newDirectory(
                                        $fileinfo->getFilename(),
                                        octdec(substr(sprintf('%o', $fileinfo->getPerms()), -4))
                                )->at($baseDir),
                                $maxFileSize
                        );
                    }

                    break;

                case 'block':
                    self::newBlock(
                            $fileinfo->getFilename(),
                            octdec(substr(sprintf('%o', $fileinfo->getPerms()), -4))
                        )->at($baseDir);
                    break;
            }
        }

        return $baseDir;
    }

    /**
     * returns a new file with given name
     *
     * @param   string  $name         name of file to create
     * @param   int     $permissions  permissions of file to create
     * @return  vfsFile
     */
    public static function newFile($name, $permissions = null)
    {
        return new vfsFile($name, $permissions);
    }

    /**
     * returns a new directory with given name
     *
     * If the name contains slashes, a new directory structure will be created.
     * The returned directory will always be the parent directory of this
     * directory structure.
     *
     * @param   string  $name         name of directory to create
     * @param   int     $permissions  permissions of directory to create
     * @return  vfsDirectory
     */
    public static function newDirectory($name, $permissions = null)
    {
        if ('/' === substr($name, 0, 1)) {
            $name = substr($name, 1);
        }

        $firstSlash = strpos($name, '/');
        if (false === $firstSlash) {
            return new vfsDirectory($name, $permissions);
        }

        $ownName   = substr($name, 0, $firstSlash);
        $subDirs   = substr($name, $firstSlash + 1);
        $directory = new vfsDirectory($ownName, $permissions);
        if (is_string($subDirs) && strlen($subDirs) > 0) {
            self::newDirectory($subDirs, $permissions)->at($directory);
        }

        return $directory;
    }

    /**
     * returns a new block with the given name
     *
     * @param   string  $name           name of the block device
     * @param   int     $permissions    permissions of block to create
     * @return vfsBlock
     */
    public static function newBlock($name, $permissions = null)
    {
        return new vfsBlock($name, $permissions);
    }

    /**
     * returns current user
     *
     * If the system does not support posix_getuid() the current user will be root (0).
     *
     * @return  int
     */
    public static function getCurrentUser()
    {
        return function_exists('posix_getuid') ? posix_getuid() : self::OWNER_ROOT;
    }

    /**
     * returns current group
     *
     * If the system does not support posix_getgid() the current group will be root (0).
     *
     * @return  int
     */
    public static function getCurrentGroup()
    {
        return function_exists('posix_getgid') ? posix_getgid() : self::GROUP_ROOT;
    }

    /**
     * use visitor to inspect a content structure
     *
     * If the given content is null it will fall back to use the current root
     * directory of the stream wrapper.
     *
     * Returns given visitor for method chaining comfort.
     *
     * @param   vfsStreamVisitor  $visitor  the visitor who inspects
     * @param   vfsStreamContent  $content  directory structure to inspect
     * @return  vfsStreamVisitor
     * @throws  \InvalidArgumentException
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/10
     */
    public static function inspect(vfsStreamVisitor $visitor, vfsStreamContent $content = null)
    {
        if (null !== $content) {
            return $visitor->visit($content);
        }

        $root = StreamWrapper::getRoot();
        if (null === $root) {
            throw new \InvalidArgumentException('No content given and no root directory set.');
        }

        return $visitor->visitDirectory($root);
    }

    /**
     * sets quota to given amount of bytes
     *
     * @param  int  $bytes
     * @since  1.1.0
     */
    public static function setQuota($bytes)
    {
        StreamWrapper::setQuota(new Quota($bytes));
    }

    /**
     * checks if vfsStream lists dotfiles in directory listings
     *
     * @return  bool
     * @since   1.3.0
     */
    public static function useDotfiles()
    {
        return self::$dotFiles;
    }

    /**
     * disable dotfiles in directory listings
     *
     * @since  1.3.0
     */
    public static function disableDotfiles()
    {
        self::$dotFiles = false;
    }

    /**
     * enable dotfiles in directory listings
     *
     * @since  1.3.0
     */
    public static function enableDotfiles()
    {
        self::$dotFiles = true;
    }
}

class_alias('bovigo\vfs\vfsStream', 'org\bovigo\vfs\vfsStream');
