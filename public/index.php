<?php

//print_r($_SERVER['REQUEST_URI'] ); exit;
try {

    $loader = new \Phalcon\Loader();

    $loader->registerNamespaces(
        array(
            'Meetingroom' => "../Meetingroom/"
        )
    );

    $loader->registerDirs(
        array(
            '../Meetingroom/',
        )
    );

    $loader->register();

    //Create a DI
    $di = new Phalcon\DI\FactoryDefault();

    $di->setShared(
        'config',
        function () {
            return new \Phalcon\Config\Adapter\Ini("../Meetingroom/config.ini");
        }
    );

    $di->set(
        'mydb_con',
        function () use ($di) {
            return new \Phalcon\Db\Adapter\Pdo\Postgresql((array)$di->get('config')->db);
        }
    );

    $di->set(
        'router',
        function () {

            $router = new \Phalcon\Mvc\Router();
            $router->setUriSource(\Phalcon\Mvc\Router::URI_SOURCE_SERVER_REQUEST_URI);
            $router->add(
                "/",
                array(
                    'namespace' => 'Meetingroom\Controller',
                    'controller' => 'Index',
                    'action' => 'index',
                )
            );

            $router->add(
                "/:controller/:action/:params",
                array(
                    "controller" => 1,
                    "action" => 2,
                    "params" => 3,
                )
            );

            $router->setDefaults(
                array(
                    'namespace' => 'Meetingroom\Controller',
                    'controller' => 'index',
                    'action' => 'index'
                )
            );

            return $router;
        }
    );

    //Setup the view component
    $di->set(
        'view',
        function () {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir('../Meetingroom/View/');
            return $view;
        }
    );

    //Setup a base URI so that all generated URIs include the "tutorial" folder
    $di->set(
        'url',
        function () {
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri('/');
            return $url;
        }
    );

    //Handle the request
    $application = new \Phalcon\Mvc\Application($di);

    // ###### ACL
    $di->setShared(
        'acl',
        function () {
            $acl = new \Phalcon\Acl\Adapter\Memory();
            $acl->setDefaultAction(Phalcon\Acl::DENY);
            $roleUsers = new \Phalcon\Acl\Role("Users");
            $roleGuests = new \Phalcon\Acl\Role("Guests");
            $acl->addRole($roleGuests);
            $acl->addRole($roleUsers);
            $userResource = new \Phalcon\Acl\Resource("User");
            $acl->addResource($userResource, ['index', 'test']);

            $acl->allow("Guests", "User", "index");
            $acl->allow("Users", "User", "test");
            return $acl;
        }
    );


    //### SESSION
    // Регистрация сервиса сессий, как "always shared"
    //    $session = $di->get('session'); // Locates the service for the first time
    //    $session = $di->getSession(); // Returns the first instantiated object
    $di->setShared(
        'session',
        function () {
            $session = new Phalcon\Session\Adapter\Files();
            $session->start();
            return $session;
        }
    );


    echo $application->handle()->getContent();
} catch (\Phalcon\Exception $e) {
    echo "PhalconException: ", $e->getMessage();
}
