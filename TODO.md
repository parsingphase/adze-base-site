# Adze To-Do list #

 - Refactor app to expose the Twig_Loader_Filesystem, and use that to prepend / append paths, using namespaces (rather than chain loader)
 - Create layout.html.twig with head, content, sidebar blocks in "site" namespace
    - Developers can override by prepending other directory in this namespace and replacing file there
    - Site specific content can go in custom composable module
 - Create blog inner templates in "blog" namespace
 - Create table schemas for user, blog with \Doctrine\DBAL\Schema\AbstractSchemaManager, \Doctrine\DBAL\Schema\Table as at http://silex.sensiolabs.org/doc/providers/security.html
 - Create cli script to make DBAL connection from `$appConfig['db.options']` and build schemas with that
 - Migrate old DB blog content
 - Implement add, update functionality to blog, using user access control (use is_granted('ROLE_ADMIN') for now)
 - Recreate phase.org template in bootstrap 3
 - Refactor user management to expose only required functionality
 - Interface to Twitter, Medium, Quora etc
 - Implement OpenID identity provider