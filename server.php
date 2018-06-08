<?php

use Aerys\{Host, Request, Response};
use function Aerys\{root, router};
use Functional as λ;
use Slamp\Slack\Channel;

require __DIR__.'/vendor/autoload.php';

function respond(Response $res, $body, int $status = 200)
{
    $res->setStatus($status);
    $res->addHeader('Content-Type', 'application/json');
    $res->end(json_encode($body));
}

//TODO encrypt slack token and get it from a file (travis) //DONE in theory
$slack_token = file_get_contents('.token');
$geekdpt = new Slamp\WebClient(getenv('$slack_token'));

$router = router();

$router->get('/team-info', function (Request $req, Response $res) use ($geekdpt) {
    $chans = yield $geekdpt->channels->listAsync(['exclude_archived' => true]);

    $chans = λ\sort($chans, function (Channel $chanA, Channel $chanB) {
        return $chanB->getMembersCount() <=> $chanA->getMembersCount();
    });

    $chans = λ\map($chans, function (Channel $chan) {
        return [
            'name' => $chan->getName(),
            'membersCount' => $chan->getMembersCount(),
            'purpose' => $chan->getPurpose(),
        ];
    });

    respond($res, ['channels' => $chans]);
});

$router->post('/get-invite', function (Request $req, Response $res) use ($geekdpt) {
    $body = json_decode(yield $req->getBody());

    try {
        yield $geekdpt->users->admin->inviteAsync($body->email);
    } catch (Exception $ex) {
        respond($res, ['error' => $ex->getMessage()], 400);
        return;
    }

    respond($res, ['err' => null]);
});

$host = (new Host)
    ->expose('0.0.0.0', 8080) //TODO put this in config
    ->name('localhost')
    ->name('geekdpt.io')
    ->use($router)
    ->use(root(__DIR__.'/web/dist'));

Amp\run(function () use ($host) {
    $logger = new class extends Aerys\Logger {
        protected function output(string $message)
        {
            //TODO print log in a file
            print "$message\n"; // log to stdout
        }
    };

    $server = (new Aerys\Bootstrapper(function () use ($host) {
        return [$host];
    }))->init($logger, []);

    $server->start();
});
