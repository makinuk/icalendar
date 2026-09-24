<?php

namespace App\Fixture;

final class Page
{
    public function show(string $name = ''): void {}

    public function addEvent(object $event): void {}

    public function setMethod(string $method): void {}
}

function unrelated(Page $page, object $anything): void
{
    $page->show();
    $page->addEvent($anything);
    $page->setMethod('REQUEST');
    $anything->setStartDate(1);
}

?>
