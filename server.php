<?php

use Aerys\{Host, Request, Response};
use function Aerys\{root, router};
use Functional as λ;
use Slamp\SlackObject\Channel;

function respond(Response $res, $body, int $status = 200) {
    $res->setStatus($status);
    $res->addHeader('Content-Type', 'application/json');
    $res->end(json_encode($body));
}

$geekdpt = new Slamp\WebClient(getenv('SLACK_TOKEN'));

$router = router();

$router->get('/team-info', function(Request $req, Response $res) use($geekdpt) {
    $chans = yield $geekdpt->channels->listAsync(['exclude_archived' => true]);

    $chans = λ\sort($chans, function(Channel $chanA, Channel $chanB) {
        return $chanB->getMembersCount() <=> $chanA->getMembersCount();
    });

    $chans = λ\map($chans, function(Channel $chan) {
        return [
            'name' => $chan->getName(),
            'membersCount' => $chan->getMembersCount(),
            'purpose' => $chan->getPurpose(),
        ];
    });

    respond($res, ['channels' => $chans]);
});

$router->post('/get-invite', function(Request $req, Response $res) use($geekdpt) {
    $body = json_decode(yield $req->getBody());

    try {
        yield $geekdpt->users->admin->inviteAsync($body->email);
    } catch(Exception $ex) {
        respond($res, ['error' => $ex->getMessage()], 400);
        return;
    }

    respond($res, ['err' => null]);
});

(new Host)
    ->expose('127.0.0.1', 8080)
    ->use($router)
    ->use(root(__DIR__.'/web/dist'));