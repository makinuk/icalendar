<?php

namespace App\Fixture;

use makinuk\ICalendar\ICalendar;
use makinuk\ICalendar\ICalEvent;

function calendar(ICalEvent $event, string $method, int $start): string
{
    $invite = new ICalendar($event, 'REQUEST');
    $feed = new ICalendar(null, 'publish');
    $single = new ICalendar($event);
    $dynamic = new ICalendar($event, $method);

    $single->setMethod('CANCEL');
    $single->setMethod($method);
    $single->setMethod('X-CUSTOM');

    $event->setStartDate(ICalendar::convertUTC($start));
    $event->setEndDate(ICalendar::convertUTC());

    $invite->show();
    $feed->show('feed.ics');

    return $single->getCalendarText();
}

?>
-----
<?php

namespace App\Fixture;

use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Enum\Method;

function calendar(Event $event, string $method, int $start): string
{
    $invite = (new Calendar($event))->setMethod(Method::Request);
    $feed = (new Calendar())->setMethod(Method::Publish);
    $single = new Calendar($event);
    $dynamic = (new Calendar($event))->setMethod(Method::from($method));

    $single->setMethod(Method::Cancel);
    $single->setMethod(Method::from($method));
    $single->setMethod(Method::from('X-CUSTOM'));

    $event->setStart($start);
    $event->setEnd(time());

    $invite->send('iCalendar.ics', asAttachment: false);
    $feed->send('feed.ics', asAttachment: false);

    return $single->render();
}

?>
