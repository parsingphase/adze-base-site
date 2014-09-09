<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 09/09/14
 * Time: 15:15
 */

namespace Phase\Blog;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class BlogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $dbFile;

    /**
     * @var Connection
     */
    protected $dbConnection;

    protected function setUp()
    {
        $dbFileSource = dirname(dirname(__DIR__)) . '/resources/blogtest.sqlite';
        $dbFile = $dbFileSource . '.tmp';

        $copied = copy($dbFileSource, $dbFile);

        if (!$copied) {
            throw new \Exception("Failed to create working copy of blogtest.sqlite");
        }

        $this->dbFile = $dbFile;

        $params = [
            'driver' => 'pdo_sqlite',
            'path' => $dbFile
        ];

        $this->dbConnection = DriverManager::getConnection($params); //, $config);

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testEnvironment()
    {
        $this->assertFileExists($this->dbFile);
        $this->assertTrue(is_file($this->dbFile), "SQlite source must be regular file");

        $this->assertTrue($this->dbConnection instanceof Connection);

        $schemaManager = $this->dbConnection->getSchemaManager();

        $tablesPresent = $schemaManager->listTableNames();

        $this->assertTrue(is_array($tablesPresent) && count($tablesPresent), 'Must get some tables');

        $requiredTables = ['blog_post'];

        foreach ($requiredTables as $table) {
            $this->assertTrue(in_array($table, $tablesPresent), "Table '$table' is required");
        }
    }

    public function testStoreBlogPost()
    {
        $blog = new Blog($this->dbConnection);
        $this->assertTrue($blog instanceof Blog);

        $blogPost = new BlogPost();

        $this->assertTrue($blogPost instanceof BlogPost);
        $blogPost->setSubject('Test blog post');
        $blogPost->setBody('Post body');

        $this->assertFalse((bool)$blogPost->getId());

        $blog->savePost($blogPost);
        $this->assertTrue((bool)$blogPost->getId());
    }

    public function testFetchBlogPost()
    {
        $rawPost = [
            'subject' => 'Fetch Me',
            'body' => 'Fascinating Content',
            'time' => date('Y-m-d h:i:s'),
            'security' => BlogPost::SECURITY_PUBLIC
        ];

        $this->dbConnection->insert('blog_post', $rawPost);

        $sql = "SELECT MIN(id) FROM blog_post";
        $presentPostId = $this->dbConnection->fetchColumn($sql);

        $this->assertTrue((bool)$presentPostId);

        $blog = new Blog($this->dbConnection);

        $newPost = $blog->fetchPostById($presentPostId);

        $this->assertTrue($newPost instanceof BlogPost);

        $rawPost = [
            'subject' => 'Fetch Me First',
            'body' => 'Earlier Fascinating Content',
            'time' => date('Y-m-d h:i:s', time() - 3600),
            'security' => BlogPost::SECURITY_PUBLIC
        ];

        $this->dbConnection->insert('blog_post', $rawPost);

        $multiPosts = $blog->fetchRecentPosts();
        $this->assertTrue(is_array($multiPosts));
        $this->assertSame(2, count($multiPosts));
        $this->assertTrue($multiPosts[0]->getTime()>$multiPosts[1]->getTime());
    }

}
