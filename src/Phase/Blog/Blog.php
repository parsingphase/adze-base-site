<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 09/09/14
 * Time: 14:10
 */

namespace Phase\Blog;


use Doctrine\DBAL\Connection;

/**
 * Access class for posts on a blog
 * @package Phase\Blog
 */
class Blog
{

    /**
     * @var Connection
     */
    protected $dbConnection;

    /**
     * Set up access class using given DB connection
     * @param Connection $dbConnection
     */
    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Store a blog post to the configured DBAL
     *
     * @param BlogPost $blogPost
     * @return bool
     */
    public function savePost(BlogPost $blogPost)
    {
        $return = false;
        if (!$blogPost->getId()) {

            if (!$blogPost->getTime()) {
                $blogPost->setTime(new \DateTime());
            }

            $insertCount = $this->dbConnection->insert(
                'blog_post',
                [
                    'time' => $blogPost->getTime()->format('Y-m-d h:i:s'),
                    'subject' => $blogPost->getSubject(),
                    'body' => $blogPost->getBody(),
                    'security' => $blogPost->getSecurity()
                ]
            );

            if ($insertCount) {
                $id = $this->dbConnection->lastInsertId();
                if ($id) {
                    $blogPost->setId($id);
                    $return = true;
                }
            }
        }

        return $return;
    }

    /**
     * Load a blog post from the configured DBAL by primary key
     *
     * @param $presentPostId
     * @return null|BlogPost
     */
    public function fetchPostById($presentPostId)
    {
        $post = null;
        $sql = 'SELECT * FROM blog_post WHERE id=?';
        $row = $this->dbConnection->fetchAssoc($sql, [$presentPostId]);
        if ($row) {
            $post = $this->createPostFromDbRow($row);
        }
        return $post;
    }

    /**
     * @param int $count
     * @return BlogPost[]
     * @throws \InvalidArgumentException
     */
    public function fetchRecentPosts($count = 5)
    {
        $posts = [];
        if (!is_int($count)) {
            throw new \InvalidArgumentException();
        }
        $sql = 'SELECT * FROM blog_post ORDER BY time DESC LIMIT ?';
        $rows = $this->dbConnection->fetchAll($sql, [$count]);
        foreach ($rows as $row) {
            $posts[] = $this->createPostFromDbRow($row);
        }
        return $posts;
    }

    /**
     * @param $row
     * @return BlogPost
     */
    protected function createPostFromDbRow($row)
    {
        $post = new BlogPost();
        $post->setId($row['id']);
        $post->setSubject($row['subject']);
        $post->setTime(new \DateTime($row['time']));
        $post->setSecurity($row['security']);
        return $post;
    }
}
