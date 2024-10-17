<?php
declare(strict_types=1);

namespace Vonage\VonagePhpServerless\Controller\SMS;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Vonage\Client;
use Vonage\Messages\Channel\SMS\SMSText;
use Vonage\VonagePhpServerless\Traits\VonageClientAware;

class SendController
{
    use VonageClientAware;

    protected mixed $db;

    public function __construct(Client $vonageClient, ContainerInterface $container)
    {
        $this->setClient($vonageClient);
        $this->db = $container->get('db');
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $sql = 'SELECT * FROM sms';
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $smsRecords = $statement->fetchAll();

        $number = $request->getParsedBody()['phone'];
        $message = $request->getParsedBody()['message'];

        $sms = new SMSText($number, $_ENV['VONAGE_FROM_NUMBER'], $message);

        try {
            $this->getClient()->messages()->send($sms);
            $apiResponse = 'Success';
        } catch (\Exception $e) {
            $apiResponse = $e->getMessage();
        }

        $view = Twig::fromRequest($request);

        return $view->render($response, 'sms.html.twig', [
            'identifier' => 'SMS Demo',
            'smsRecords' => $smsRecords,
            'response' => $apiResponse
        ]);
    }
}