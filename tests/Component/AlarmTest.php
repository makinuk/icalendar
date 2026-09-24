<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests\Component;

use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Enum\AlarmAction;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Tests\TestCase;
use Makinuk\ICalendar\Value\Duration;

final class AlarmTest extends TestCase
{
    public function testDefaultTriggerIsFifteenMinutesBefore(): void
    {
        self::assertSame(self::lines(
            'BEGIN:VALARM',
            'ACTION:DISPLAY',
            'TRIGGER:-PT15M',
            'DESCRIPTION:Reminder',
            'END:VALARM',
        ), Alarm::display()->render());
    }

    public function testRelativeTriggers(): void
    {
        self::assertStringContainsString('TRIGGER:-P1DT2H', Alarm::display()->before(days: 1, hours: 2)->render());
        self::assertStringContainsString('TRIGGER:PT5M', Alarm::display()->after(minutes: 5)->render());
        self::assertStringContainsString('TRIGGER:PT0S', Alarm::display()->before()->render());
        self::assertStringContainsString(
            'TRIGGER;RELATED=END:-PT10M',
            Alarm::display()->before(minutes: 10)->relativeToEnd()->render(),
        );
    }

    public function testAbsoluteTrigger(): void
    {
        self::assertStringContainsString(
            'TRIGGER;VALUE=DATE-TIME:20261001T085500Z',
            Alarm::audio()->at(self::utc('2026-10-01 08:55'))->render(),
        );
    }

    public function testAudioAlarm(): void
    {
        $alarm = Alarm::audio('https://example.com/bell.mp3')->repeat(2, Duration::minutes(5));

        self::assertSame(AlarmAction::Audio, $alarm->getAction());
        self::assertSame(self::lines(
            'BEGIN:VALARM',
            'ACTION:AUDIO',
            'TRIGGER:-PT15M',
            'ATTACH:https://example.com/bell.mp3',
            'REPEAT:2',
            'DURATION:PT5M',
            'END:VALARM',
        ), $alarm->render());
    }

    public function testEmailAlarm(): void
    {
        $alarm = Alarm::email('Tomorrow: kickoff', 'Agenda attached', 'team@example.com')->before(days: 1);

        self::assertSame(self::lines(
            'BEGIN:VALARM',
            'ACTION:EMAIL',
            'TRIGGER:-P1D',
            'DESCRIPTION:Agenda attached',
            'SUMMARY:Tomorrow: kickoff',
            'ATTENDEE:mailto:team@example.com',
            'END:VALARM',
        ), $alarm->render());
    }

    public function testEmailAlarmNeedsRecipients(): void
    {
        $this->expectException(ValidationException::class);

        Alarm::email('Subject', 'Body')->render();
    }

    public function testDisplayAlarmNeedsDescription(): void
    {
        $this->expectException(ValidationException::class);

        Alarm::display('')->render();
    }
}
