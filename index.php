<?php
declare(strict_types=1);

require '../vendor/autoload.php';

$f3 = \Base::instance();
$f3->set('DEBUG',    3);
$f3->set('UI',       'templates/');
$f3->set('CACHE',    false); // true in produzione
$f3->set('AUTOLOAD', 'app/');

// Registra i tag UI (il path dei componenti si può omettere se è ../components)
UI::register(__DIR__ . '/components');

$f3->route('GET /', function (\Base $f3) {
    $f3->set('pageTitle', 'Gestionale');
    $f3->set('user', ['nome' => 'Mario', 'email' => 'mario@esempio.it']);
    $f3->set('saved', true);
    echo \Template::instance()->render('demo.html');
});

$f3->route('POST /salva', function (\Base $f3) {
    $f3->set('pageTitle', 'Gestionale');
    $f3->set('user', ['nome' => $f3->get('POST.nome'), 'email' => $f3->get('POST.email')]);
    $f3->set('saved', true);
    echo \Template::instance()->render('demo.html');
});

$f3->run();
