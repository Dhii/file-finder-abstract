<?php

namespace Dhii\File\Finder;

use SplFileInfo;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use CallbackFilterIterator;
use RecursiveIterator;
use Traversable;
use Iterator;

/**
 * Common functionality for file finders.
 *
 * @since [*next-version*]
 */
abstract class AbstractFileFinder
{
    /**
     * The root directory path.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $rootDir;

    /**
     * How deep to recurse into the root directory.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $maxDepth;

    /**
     * The RegEx to use for validating the file name.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $filenameRegex;

    /**
     * The callback that is optionally used to filter files.
     *
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $callbackFilter;

    /**
     * Parameter-less constructor.
     *
     * Call this in the real constructor.
     *
     * @since [*next-version*]
     */
    protected function _construct()
    {
    }

    /**
     * Assigns the maximal directory depth to recurse into when locating files.
     *
     * @since [*next-version*]
     *
     * @param int $depth The depth to set.
     *
     * @return $this
     */
    protected function _setMaxDepth($depth)
    {
        $this->maxDepth = $depth;

        return $this;
    }

    /**
     * Retrieves the maximal directory depth to recurse into when locating files.
     *
     * @since [*next-version*]
     *
     * @return int The depth.
     */
    protected function _getMaxDepth()
    {
        return $this->maxDepth;
    }

    /**
     * Retrieves the root directory, in which to look for files.
     *
     * @since [*next-version*]
     *
     * @return string The directory path.
     */
    protected function _getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * Assigns the root directory, in which to look for files.
     *
     * @since [*next-version*]
     *
     * @param string $dirPath The directory path.
     *
     * @return $this
     */
    protected function _setRootDir($dirPath)
    {
        $this->rootDir = $dirPath;

        return $this;
    }

    /**
     * Retrieves the regular expression used to filter filenames.
     *
     * @since [*next-version*]
     *
     * @return string|null The expression.
     */
    protected function _getFilenameRegex()
    {
        return $this->filenameRegex;
    }

    /**
     * Assigns the regular expression used to filter filenames.
     *
     * @since [*next-version*]
     *
     * @param string|null $regex The expression.
     *
     * @return $this
     */
    protected function _setFilenameRegex($regex)
    {
        $this->filenameRegex = $regex;

        return $this;
    }

    /**
     * Retrieves the filter that is optionally used to filter files.
     *
     * @since [*next-version*]
     *
     * @return callable|null The callback.
     */
    protected function _getCallbackFilter()
    {
        return $this->callbackFilter;
    }

    /**
     * Assigns the filter that is optionally used to filter files.
     *
     * @since [*next-version*]
     *
     * @param callable|null $filter The callback.
     *
     * @throws InvalidArgumentException If the filter is not a callable.
     *
     * @return $this
     */
    protected function _setCallbackFilter($filter)
    {
        if (!is_callable($filter, true) && !is_null($filter)) {
            throw new InvalidArgumentException('Filter must be a callable');
        }

        $this->callbackFilter = $filter;

        return $this;
    }

    /**
     * Retrieves a list of file paths.
     *
     * @since [*next-version*]
     *
     * @return string[]|Traversable The file set.
     */
    protected function _getPaths()
    {
        $directories = $this->_createDirectoryIterator($this->_getRootDir());
        $flattened   = $this->_createRecursiveIteratorIterator($directories);
        $flattened->setMaxDepth($this->_getMaxDepth());
        $filter = $this->_createFilterIterator($flattened, function (SplFileInfo $current, $key, Iterator $iterator) {
            return $this->_filterFile($current);
        });

        return $filter;
    }

    /**
     * Determines whether or not a file is allowed to be "found".
     *
     * @since [*next-version*]
     *
     * @param SplFileInfo $fileInfo The object that represents information about a file.
     *
     * @return bool True if the file is allowed; false otherwise.
     */
    protected function _filterFile(SplFileInfo $fileInfo)
    {
        if (!$fileInfo->isFile()) {
            return false;
        }

        if (($expr = $this->_getFilenameRegex()) && !preg_match($expr, $fileInfo->getPathname())) {
            return false;
        }

        if (($callback = $this->_getCallbackFilter()) && !call_user_func_array($callback, array($fileInfo))) {
            return false;
        }

        return true;
    }

    /**
     * Creates a new directory iterator.
     *
     * @since [*next-version*]
     *
     * @param string $directory Path to the directory.
     *
     * @return RecursiveIterator The new iterator.
     */
    protected function _createDirectoryIterator($directory)
    {
        return new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
    }

    /**
     * Creates a new iterator to iterate over a recursive iterator.
     *
     * @since [*next-version*]
     *
     * @param Traversable $iterator The recursive iterator to iterate over.
     *
     * @return RecursiveIteratorIterator The new iterator.
     */
    protected function _createRecursiveIteratorIterator(Traversable $iterator)
    {
        return new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * Creates a new iterator to filter items of another iterator.
     *
     * @since [*next-version*]
     *
     * @param Traversable $iterator The iterator, the items of which to filter.
     * @param callable    $callback The callback to filter the iterator's items.
     *
     * @return OuterIterator The new iterator.
     */
    protected function _createFilterIterator(Traversable $iterator, $callback)
    {
        return new CallbackFilterIterator($iterator, $callback);
    }
}
