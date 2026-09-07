<?php declare(strict_types = 1);

namespace idoit\Controller;

interface Responseable
{
    /**
     * Preparation method
     *
     * @return void
     */
    public function pre();

    /**
     * Post the response
     *
     * @return void
     */
    public function post();

    /**
     * @return array
     */
    public function getResponse();
}
