<?php

use DI\Bridge\Slim\Bridge;
use DI\Container;
use Laminas\Diactoros\Response\JsonResponse;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Spatie\Ignition\Ignition as IgnitionAlias;
use Twig\Extension\DebugExtension;
use Vonage\Client;
use Vonage\VonagePhpServerless\Controller\Home\IndexController as HomeIndexController;
use Vonage\VonagePhpServerless\Controller\SMS\IndexController as SMSIndexController;
use Vonage\VonagePhpServerless\Controller\Verify\IndexController as VerifyIndexController;
use Vonage\VonagePhpServerless\Controller\Verify\SendController as VerifySendController;
use Vonage\VonagePhpServerless\Controller\SMS\SendController as SMSSendController;
use Vonage\VonagePhpServerless\Controller\SMS\IncomingSmsController as SMSIncomingController;
use Vonage\VonagePhpServerless\Controller\Verify\CodeController as VerifyCodeController;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

IgnitionAlias::make()->setTheme('dark')->register();

$container = new Container();
$container->set(Client::class, function () {
    $applicationId = $_ENV['API_APPLICATION_ID'];
    $privateKey = $_ENV['PRIVATE_KEY'];
    $credentials = new Client\Credentials\Keypair($privateKey, $applicationId);

    return new Client($credentials);
});

$container->set('db', function () {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../data.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
});

$app = Bridge::create($container);
$twig = Twig::create(
    __DIR__ . '/../templates',
    [
        'cache' => false,
        'debug' => true,
    ]);

$app->add(TwigMiddleware::create($app, $twig));
$twig->addExtension(new DebugExtension());

$app->get('/', HomeIndexController::class)->setName('home');
$app->get('/sms', SMSIndexController::class)->setName('sms');
$app->post('/sms/send',SMSSendController::class)->setName('smsSend');
$app->post('/sms/incoming',SMSIncomingController::class)->setName('smsIncoming');
$app->get('/verify', VerifyIndexController::class)->setName('verify');
$app->post('/verify/send', VerifySendController::class)->setName('verifySend');
$app->post('/verify/code', VerifyCodeController::class)->setName('verifyCode');

// VCR Health Check otherwise will not deploy
$app->map(['GET', 'POST'], '/_/health', function() {
    return new JsonResponse(['status' => 'ok']);
});

$app->run();