<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 10/09/14
 * Time: 14:23
 */

namespace Phase\Blog\Silex;


use Phase\Adze\Application;
use Phase\Blog\Blog;

class BlogController
{
    /**
     * @var Blog
     */
    protected $blog;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Blog $blog, Application $app)
    {
        $this->blog = $blog;
        $this->app = $app;
    }

    public function indexAction()
    {
        $posts = $this->blog->fetchRecentPosts();
        return $this->app->render('blog/index.html.twig', ['posts'=>$posts]);
    }

}
