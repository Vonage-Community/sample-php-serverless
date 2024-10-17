<?php
declare(strict_types=1);

namespace Vonage\VonagePhpServerless\Controller\Home;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class IndexController
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'home.html.twig', [
            'identifier' => 'Welcome to the Vonage PHP Serverless Demo',
        ]);
    }
}