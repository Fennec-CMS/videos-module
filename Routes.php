<?php
use Fennec\Library\Router;

$routes = array(
    array(
        'name' => 'videos',
        'route' => '/videos/',
        'module' => 'Videos',
        'controller' => 'Index',
        'action' => 'videos',
        'layout' => 'Default'
    ),
    array(
        'name' => 'admin-videos',
        'route' => '/admin/videos/',
        'module' => 'Videos',
        'controller' => 'Admin\\Index',
        'action' => 'index',
        'layout' => 'Admin/Default'
    ),
    array(
        'name' => 'admin-video-new',
        'route' => '/admin/videos/create/',
        'module' => 'Videos',
        'controller' => 'Admin\\Index',
        'action' => 'form',
        'layout' => 'Admin/Default'
    ),
    array(
        'name' => 'admin-video-edit',
        'route' => '/admin/videos/edit/([0-9]+)/',
        'params' => array(
            'id'
        ),
        'module' => 'Videos',
        'controller' => 'Admin\\Index',
        'action' => 'form',
        'layout' => 'Admin/Default'
    ),
    array(
        'name' => 'admin-video-delete',
        'route' => '/admin/videos/delete/([0-9]+)/',
        'params' => array(
            'id'
        ),
        'module' => 'Videos',
        'controller' => 'Admin\\Index',
        'action' => 'delete',
        'layout' => null
    )
);

foreach ($routes as $route) {
    Router::addRoute($route);
}
