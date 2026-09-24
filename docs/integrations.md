# Integrations

The library has no framework dependency. `render()` returns a string and `getContentType()` the
matching content type, which is all a response object needs.

## Plain PHP

```php
$calendar->send('event.ics');                        // download
$calendar->send('event.ics', asAttachment: false);   // open inline (feeds, webcal)
```

`send()` writes the `Content-Type`, `Content-Disposition` and `Content-Length` headers and echoes the
content. Call it before any other output.

## Laravel

```php
use Illuminate\Http\Response;

public function download(Booking $booking): Response
{
    $calendar = new Calendar(
        Event::create($booking->title, $booking->starts_at, $booking->ends_at)
            ->setUid('booking-' . $booking->id . '@' . request()->getHost()),
    );

    return response($calendar->render(), 200, [
        'Content-Type' => $calendar->getContentType(),
        'Content-Disposition' => 'attachment; filename="booking.ics"',
    ]);
}
```

Carbon instances can be passed directly: they implement `DateTimeInterface`.

## Symfony

```php
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

#[Route('/bookings/{id}.ics')]
public function download(Booking $booking): Response
{
    $calendar = new Calendar(Event::create($booking->getTitle(), $booking->getStart(), $booking->getEnd()));

    return new Response($calendar->render(), 200, [
        'Content-Type' => $calendar->getContentType(),
        'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'booking.ics'),
    ]);
}
```

## PSR-7 / PSR-15

```php
return $responseFactory->createResponse()
    ->withHeader('Content-Type', $calendar->getContentType())
    ->withHeader('Content-Disposition', 'attachment; filename="event.ics"')
    ->withBody($streamFactory->createStream($calendar->render()));
```

## Subscribable feeds

A feed is a URL that always returns the current calendar. Clients poll it and update events.

```php
$calendar = (new Calendar())
    ->setName('My bookings')
    ->setRefreshInterval(Duration::hours(1));   // a hint; Google Calendar polls less often

foreach ($bookings as $booking) {
    $calendar->add(
        (new Event('booking-' . $booking->id . '@example.com'))   // stable UIDs are essential
            ->setSummary($booking->title)
            ->setStart($booking->start)
            ->setEnd($booking->end)
            ->setLastModified($booking->updatedAt),
    );
}
```

- Link to the feed with `webcal://example.com/feeds/abc123.ics` so that a click opens the calendar app.
- Feeds are public to anyone with the URL: use an unguessable token rather than a user id.
- Omit `METHOD`, or use `Method::Publish`.
- Keep the feed small: past events can usually be dropped after a few months.

A full runnable feed is in [examples/05-calendar-feed.php](../examples/05-calendar-feed.php).

## "Add to calendar" buttons

A link to an `.ics` download works in Apple Calendar, Outlook and most mobile devices. Google Calendar
on the web imports `.ics` files only through its settings page, so many sites show an extra
"Add to Google Calendar" link built from Google's own URL format next to the `.ics` link.
