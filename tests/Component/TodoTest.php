<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests\Component;

use Makinuk\ICalendar\Component\Todo;
use Makinuk\ICalendar\Enum\TodoStatus;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Tests\TestCase;
use Makinuk\ICalendar\Value\Duration;

final class TodoTest extends TestCase
{
    private static function todo(): Todo
    {
        return (new Todo('todo-1@example.com'))->setTimestamp(self::utc('2026-09-01 08:00:00'));
    }

    public function testTodoWithDueDate(): void
    {
        $todo = self::todo()
            ->setSummary('Send the report')
            ->setDue(self::utc('2026-10-15 17:00:00'))
            ->setPriority(1)
            ->setStatus(TodoStatus::InProcess)
            ->setPercentComplete(40);

        self::assertSame(self::lines(
            'BEGIN:VTODO',
            'UID:todo-1@example.com',
            'DTSTAMP:20260901T080000Z',
            'DUE:20261015T170000Z',
            'SUMMARY:Send the report',
            'PRIORITY:1',
            'PERCENT-COMPLETE:40',
            'STATUS:IN-PROCESS',
            'END:VTODO',
        ), $todo->render());
    }

    public function testTodoWithoutAnyDateIsValid(): void
    {
        self::assertStringContainsString('SUMMARY:Someday', Todo::create('Someday')->render());
    }

    public function testMarkCompleted(): void
    {
        $todo = self::todo()->markCompleted(self::utc('2026-10-14 10:00:00'));

        self::assertSame(TodoStatus::Completed, $todo->getStatus());
        self::assertStringContainsString("COMPLETED:20261014T100000Z\r\nPERCENT-COMPLETE:100\r\nSTATUS:COMPLETED\r\n", $todo->render());
    }

    public function testAllDayDueDate(): void
    {
        $output = self::todo()->setAllDay()->setStart('2026-10-01')->setDue('2026-10-15')->render();

        self::assertStringContainsString("DTSTART;VALUE=DATE:20261001\r\nDUE;VALUE=DATE:20261015\r\n", $output);
    }

    public function testDurationRequiresStart(): void
    {
        $this->expectException(ValidationException::class);

        self::todo()->setDuration(Duration::days(2))->render();
    }

    public function testDueBeforeStartIsRejected(): void
    {
        $this->expectException(ValidationException::class);

        self::todo()->setStart('2026-10-15')->setDue('2026-10-01')->render();
    }

    public function testPercentCompleteRange(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::todo()->setPercentComplete(101);
    }
}
