<?php

namespace idoit\AddOn\Manager;

use isys_application;

class Downloader
{
    /**
     * @return void
     * @throws \Exception
     */
    public function download($downloadLink, $target)
    {
        return isys_application::instance()->container->get('http_client')
            ->request($downloadLink, 'GET', [
                'sink' => $target,
                'timeout' => 120
            ]);
    }
}
