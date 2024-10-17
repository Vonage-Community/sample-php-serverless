<?php
declare(strict_types=1);

namespace Vonage\VonagePhpServerless\Controller\SMS;

use JetBrains\PhpStorm\NoReturn;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vonage\Client;
use Vonage\VonagePhpServerless\Traits\VonageClientAware;

class IncomingSmsController
{
    use VonageClientAware;

    private mixed $db;

    public function __construct(Client $vonageClient, ContainerInterface $container)
    {
        $this->setClient($vonageClient);
        $this->db = $container->get('db');
    }

    #[NoReturn] public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $number = $data['from'];
        $message = $data['text'];

        $sql = "INSERT INTO sms (number, message) VALUES (:number, :message)";
        $sqlStatement = $this->db->prepare($sql);
        $sqlStatement->execute(['number' => $number, 'message' => $message]);

        return $response;
    }
}