<?php

namespace Proxy\Service\Controllers;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class DefaultController
{
    protected $requestStack;

    public function index()
    {
        $data = json_decode($this->getRequestStack()->getCurrentRequest()->getContent());
        return json_decode((new Client())
            ->get('https://api.github.com/search/repositories?' . http_build_query($data))
            ->getBody()
            ->getContents());
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
    }
}