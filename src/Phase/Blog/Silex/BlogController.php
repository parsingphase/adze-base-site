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
use Phase\Blog\BlogPost;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        return $this->app->render('@blog/index.html.twig', ['posts' => $posts]);
    }

    public function singlePostAction($uid, $slug)
    {
        //TODO check slug
        $post = $this->blog->fetchPostById($uid);
        return $this->app->render('@blog/post.html.twig', ['post' => $post]);
    }

    public function archiveAction()
    {
        $posts = $this->blog->fetchAllPostsNoBody();
        return $this->app->render('@blog/archive.html.twig', ['posts' => $posts]);
    }

    public function newPostAction(Request $request)
    {
        // There may be neater ways of doing this?
        if (!$this->app->getSecurityContext()->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException;
        }

        //Forms ref: http://symfony.com/doc/2.5/book/forms.html

        $data = array(
            'time' => new \DateTime()
        );

        $formBuilder = $this->app->getFormFactory()->createBuilder('form', $data)
            ->add('subject')
            ->add('body', 'textarea')
            ->add('time', 'datetime')
            ->add('save', 'submit')
            ->setAction($this->app->url('blog.newPost'));

        /* @var FormBuilder $formBuilder Not sure why PhpStorm chokes there */
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            // do something with the data

            $blogPost = new BlogPost();
            $blogPost->setSubject($data['subject'])->
                setBody($data['body'])->
                setTime($data['time'])->
                setSecurity(BlogPost::SECURITY_PUBLIC);

            $this->blog->savePost($blogPost);

            $newBlogId = $blogPost->getId();

            // redirect somewhere
            return $this->app->redirect(
                $this->app->path(
                    'blog.post',
                    ['uid' => $newBlogId, 'slug' => strtolower($blogPost->getSubject())]
                )
            );
        }

        return $this->app->render('@blog/newPost.html.twig', ['newPostForm' => $form->createView()]);

    }

}
