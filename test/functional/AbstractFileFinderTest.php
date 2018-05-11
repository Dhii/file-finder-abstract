<?php

namespace Dhii\File\Finder\FuncTest;

use Xpmock\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use SplFileInfo;

/**
 * Tests {@see \RebelCode\Modular\Locator\AbstractFileLocator}.
 *
 * @since [*next-version*]
 */
class AbstractFileFinderTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\\File\\Finder\\AbstractFileFinder';

    /**
     * Basename of the files to look for. No extension.
     *
     * @since [*next-version*]
     */
    const TEST_FILE_BASENAME = 'MyFile';

    /**
     * A string which will appear first in content of test files.
     *
     * @since [*next-version*]
     */
    const TEST_CONTENT_PREFIX = 'test-content-';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return \RebelCode\Modular\Locator\AbstractFileLocator
     */
    public function createInstance($rootDir = '', $maxDepth = 1, $filePattern = null, $callbackFilter = null)
    {
        $mock = $this->mock(static::TEST_SUBJECT_CLASSNAME)
            ->new();

        $reflection = $this->reflect($mock);
        $reflection->_setRootDir($rootDir);
        $reflection->_setMaxDepth($maxDepth);
        $reflection->_setFilenameRegex($filePattern);
        $reflection->_setCallbackFilter($callbackFilter);

        return $mock;
    }

    /**
     * Tests whether a correct instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInstanceOf(static::TEST_SUBJECT_CLASSNAME, $subject, 'Could not create an correct instance of the test subject');
    }

    /**
     * Tests whether the locator can locate files on the first level of dir tree.
     *
     * @since [*next-version*]
     */
    public function testCanLocateFilesLvl1()
    {
        $fs = $this->_createFilesystem();
        $subject = $this->createInstance($fs->url(), 1, sprintf('/%1$s*\.[^\s]*/', self::TEST_FILE_BASENAME));

        $reflection = $this->reflect($subject);
        $paths = $reflection->_getPaths();
        $paths = iterator_to_array($paths);
        $this->assertCount(1, $paths, 'Wrong number of files found');
    }

    /**
     * Tests whether the locator can locate files down to 2 levels of the dir tree, and using callback filter.
     *
     * @since [*next-version*]
     */
    public function testCanLocateFilesLvl2Callback()
    {
        $fs = $this->_createFilesystem();
        $subject = $this->createInstance($fs->url(), 2, sprintf('/%1$s\.[^\s]*/', self::TEST_FILE_BASENAME), function (SplFileInfo $fileInfo) {
            $contents = file_get_contents($fileInfo->getPathname());

            return strpos($contents, static::TEST_CONTENT_PREFIX) !== false;
        });

        $reflection = $this->reflect($subject);
        $paths = $reflection->_getPaths();
        $paths = iterator_to_array($paths);
        $this->assertCount(3, $paths, 'Wrong number of files found');
    }

    /**
     * A mock filesystem.
     *
     * @return vfsStreamDirectory
     */
    protected function _createFilesystem()
    {
        $fs = vfsStream::setup('vendor');
        $testContentPrefix = static::TEST_CONTENT_PREFIX;
        $testFileName = self::TEST_FILE_BASENAME;

        vfsStream::create(array(
            'rebelcode' => array(
                'awesome-library-module' => array(
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.%2$s', $testFileName, 'json') => '123',
                ),
                'awesome-feature-module' => array(
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.%2$s', $testFileName, 'php') => uniqid($testContentPrefix),
                ),
                'non-module-lib' => array(
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                ),
            ),
            'atlassian' => array(
                'awesome-library-module' => array(
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.%2$s', $testFileName, 'json') => uniqid($testContentPrefix),
                ),
                'non-module-lib' => array(
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                ),
                'awesome-feature-module' => array(
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.php', uniqid('some-file-')) => uniqid($testContentPrefix),
                    sprintf('%1$s.%2$s', $testFileName, 'php') => '123',
                ),
            ),
            sprintf('%1$s.%2$s', $testFileName, 'php') => uniqid($testContentPrefix),
            uniqid('some-file-1-') => uniqid($testContentPrefix),
            uniqid('some-file-2-') => uniqid($testContentPrefix),
        ), $fs);

        // Uncommend below line to print directory structure.
//        vfsStream::inspect(new vfsStreamPrintVisitor(), $fs);

        return $fs;
    }
}
