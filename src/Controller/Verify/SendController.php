<?php
declare(strict_types=1);

namespace Vonage\VonagePhpServerless\Controller\Verify;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Vonage\Client;
use Vonage\Verify2\Request\SMSRequest;
use Vonage\Verify2\Request\VoiceRequest;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;
use Vonage\VonagePhpServerless\Traits\VonageClientAware;

class SendController
{
    use VonageClientAware;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $number = $request->getParsedBody()['phone'];
        $deliveryMethods = $request->getParsedBody()['method'];

        if (in_array('sms', $deliveryMethods) && in_array('phone', $deliveryMethods)) {
            $verifyRequest = new SMSRequest($number, 'VonageServerless');
            $voiceWorkflow = new VerificationWorkflow(VerificationWorkflow::WORKFLOW_VOICE, $number);
            $verifyRequest->addWorkflow($voiceWorkflow);
        }

        if (in_array('sms', $deliveryMethods) && !in_array('phone', $deliveryMethods)) {
            $verifyRequest = new SMSRequest($number, 'VonageServerless');
        }

        if (!in_array('sms', $deliveryMethods) && in_array('phone', $deliveryMethods)) {
            $verifyRequest = new VoiceRequest($number, 'VonageServerless');
        }

        $apiRequest = $this->getClient()->verify2()->startVerification($verifyRequest);
        $requestId = $apiRequest['request_id'];

        $view = Twig::fromRequest($request);

        return $view->render($response, 'verify.html.twig', [
            'identifier' => 'Verify Demo',
            'page' => 'sent',
            'requestId' => $requestId
        ]);
    }
}