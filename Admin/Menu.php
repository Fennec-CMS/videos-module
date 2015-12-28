<?php
return array(
    'Videos' => array(
        'route' => 'admin-videos',
        'icon' => 'fa fa-youtube-play',
        'subitems' => array(
            $this->translate('New video') => 'admin-video-new',
            $this->translate('List') => 'admin-videos'
        )
    )
);
