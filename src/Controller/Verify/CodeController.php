<?php
declare(strict_types=1);

namespace Vonage\VonagePhpServerless\Controller\Verify;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Vonage\Client;
use Vonage\VonagePhpServerless\Traits\VonageClientAware;

class CodeController
{
    use VonageClientAware;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $code = $request->getParsedBody()['code'];
        $requestId = $request->getParsedBody()['request_id'];

        try {
            $result = $this->getClient()->verify2()->check($requestId, $code);
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        $view = Twig::fromRequest($request);

        return $view->render($response, 'verify.html.twig', [
            'identifier' => 'Verify Demo',
            'page' => 'completed',
            'result' => $result,
        ]);
    }
}