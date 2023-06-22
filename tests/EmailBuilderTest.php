<?php

declare(strict_types=1);

namespace Zaphyr\MailTests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Zaphyr\Mail\EmailBuilder;

class EmailBuilderTest extends TestCase
{
    /**
     * @var EmailBuilder
     */
    protected EmailBuilder $emailBuilder;

    public function setUp(): void
    {
        $this->emailBuilder = new EmailBuilder(new Email());
    }

    public function tearDown(): void
    {
        unset($this->emailBuilder);
    }

    /* -------------------------------------------------
     * FROM
     * -------------------------------------------------
     */

    public function testFromWithString(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->from('from@example.com'));
        self::assertEquals(new Address('from@example.com'), $this->emailBuilder->getSymfonyEmail()->getFrom()[0]);
    }

    public function testFromWithAddressObject(): void
    {
        $address = new Address('from@example.com');

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->from($address));
        self::assertEquals([$address], $this->emailBuilder->getSymfonyEmail()->getFrom());
    }

    /* -------------------------------------------------
     * SENDER
     * -------------------------------------------------
     */

    public function testSenderWithString(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->sender('sender@example.com'));
        self::assertEquals(new Address('sender@example.com'), $this->emailBuilder->getSymfonyEmail()->getSender());
    }

    public function testSenderWithAddressObject(): void
    {
        $address = new Address('sender@example.com');

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->sender($address));
        self::assertEquals($address, $this->emailBuilder->getSymfonyEmail()->getSender());
    }

    /* -------------------------------------------------
     * RETURN PATH
     * -------------------------------------------------
     */

    public function testReturnPath(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->returnPath('returnPath@example.com'));
        self::assertEquals(new Address('returnPath@example.com'), $this->emailBuilder->getSymfonyEmail()->getReturnPath());
    }

    /* -------------------------------------------------
     * REPLY TO
     * -------------------------------------------------
     */

    public function testReplyToWithString(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->replyTo('replyTo@example.com'));
        self::assertEquals(new Address('replyTo@example.com'), $this->emailBuilder->getSymfonyEmail()->getReplyTo()[0]);
    }

    public function testReplyToWithAddressObject(): void
    {
        $address = new Address('replyTo@example.com');

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->replyTo($address));
        self::assertEquals([$address], $this->emailBuilder->getSymfonyEmail()->getReplyTo());
    }

    public function testReplyToWithArrayOfAddressObjects(): void
    {
        $addresses = ['replyTo1@example.com', new Address('replyTo2@example.com')];

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->replyTo($addresses));
        self::assertEquals(
            [new Address('replyTo1@example.com'), new Address('replyTo2@example.com')],
            $this->emailBuilder->getSymfonyEmail()->getReplyTo()
        );
    }

    /* -------------------------------------------------
     * TO
     * -------------------------------------------------
     */

    public function testToWithString(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->to('to@example.com'));
        self::assertEquals(new Address('to@example.com'), $this->emailBuilder->getSymfonyEmail()->getTo()[0]);
    }

    public function testToWithAddressObject(): void
    {
        $address = new Address('to@example.com');

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->to($address));
        self::assertEquals([$address], $this->emailBuilder->getSymfonyEmail()->getTo());
    }

    public function testToWithArrayOfAddressObjects(): void
    {
        $addresses = ['to1@example.com', new Address('to2@example.com')];

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->to($addresses));
        self::assertEquals(
            [new Address('to1@example.com'), new Address('to2@example.com')],
            $this->emailBuilder->getSymfonyEmail()->getTo()
        );
    }

    public function testToWithOverride(): void
    {
        $this->emailBuilder->to(new Address('to@example.com'));
        $this->emailBuilder->to('overrideTo@example.com', true);

        self::assertEquals(new Address('overrideTo@example.com'), $this->emailBuilder->getSymfonyEmail()->getTo()[0]);
    }

    /* -------------------------------------------------
     * CC
     * -------------------------------------------------
     */

    public function testCcWithString(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->cc('cc@example.com'));
        self::assertEquals(new Address('cc@example.com'), $this->emailBuilder->getSymfonyEmail()->getCc()[0]);
    }

    public function testCcWithAddressObject(): void
    {
        $address = new Address('cc@example.com');

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->cc($address));
        self::assertEquals([$address], $this->emailBuilder->getSymfonyEmail()->getCc());
    }

    public function testCcWithArrayOfAddressObjects(): void
    {
        $addresses = ['cc1@example.com', new Address('cc2@example.com')];

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->cc($addresses));
        self::assertEquals(
            [new Address('cc1@example.com'), new Address('cc2@example.com')],
            $this->emailBuilder->getSymfonyEmail()->getCc()
        );
    }

    public function testCcWithOverride(): void
    {
        $this->emailBuilder->cc(new Address('cc@example.com'));
        $this->emailBuilder->cc('overrideCc@example.com', true);

        self::assertEquals(new Address('overrideCc@example.com'), $this->emailBuilder->getSymfonyEmail()->getCc()[0]);
    }

    /* -------------------------------------------------
     * BCC
     * -------------------------------------------------
     */

    public function testBccWithString(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->bcc('john@doe.com'));
        self::assertEquals(new Address('john@doe.com'), $this->emailBuilder->getSymfonyEmail()->getBcc()[0]);
    }

    public function testBccWithAddressObject(): void
    {
        $address = new Address('john@doe.com');

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->bcc($address));
        self::assertEquals([$address], $this->emailBuilder->getSymfonyEmail()->getBcc());
    }

    public function testBccWithArrayOfAddressObjects(): void
    {
        $addresses = ['john@doe.com', new Address('jane@doe.com')];

        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->bcc($addresses));
        self::assertEquals(
            [new Address('john@doe.com'), new Address('jane@doe.com')],
            $this->emailBuilder->getSymfonyEmail()->getBcc()
        );
    }

    public function testBccWithOverride(): void
    {
        $this->emailBuilder->bcc(new Address('bcc@example.com'));
        $this->emailBuilder->bcc('overrideBcc@example.com', true);

        self::assertEquals(new Address('overrideBcc@example.com'), $this->emailBuilder->getSymfonyEmail()->getBcc()[0]);
    }

    /* -------------------------------------------------
     * SUBJECT
     * -------------------------------------------------
     */

    public function testSubject(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->subject($subject = 'Subject'));
        self::assertEquals($subject, $this->emailBuilder->getSymfonyEmail()->getSubject());
    }

    /* -------------------------------------------------
     * DATE
     * -------------------------------------------------
     */

    public function testDate(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->date($date = new DateTimeImmutable()));
        self::assertEquals($date, $this->emailBuilder->getSymfonyEmail()->getDate());
    }

    /* -------------------------------------------------
     * PRIORITY
     * -------------------------------------------------
     */

    public function testPriority(): void
    {
        self::assertInstanceOf(EmailBuilder::class, $this->emailBuilder->priority($priority = 3));
        self::assertEquals($priority, $this->emailBuilder->getSymfonyEmail()->getPriority());
    }

    /* -------------------------------------------------
     * ATTACH FILE
     * -------------------------------------------------
     */

    public function testAttachFile(): void
    {
        file_put_contents($file = __DIR__ . '/foo.jpg', $body = 'image contents');

        $this->emailBuilder->attachFile($file, 'bar.jpg', 'image/png');
        $attachment = $this->emailBuilder->getSymfonyEmail()->getAttachments()[0];

        self::assertSame($body, $attachment->getBody());
        self::assertSame(
            'Content-Type: image/png; name=bar.jpg',
            $attachment->getPreparedHeaders()->toArray()[0]
        );
        self::assertSame(
            'Content-Disposition: attachment; name=bar.jpg; filename=bar.jpg',
            $attachment->getPreparedHeaders()->toArray()[2]
        );

        unlink($file);
    }

    /* -------------------------------------------------
     * ATTACH DATA
     * -------------------------------------------------
     */

    public function testAttachData(): void
    {
        $this->emailBuilder->attachData($body = 'image contents', 'foo.jpg', 'image/jpg');
        $attachment = $this->emailBuilder->getSymfonyEmail()->getAttachments()[0];

        self::assertSame($body, $attachment->getBody());
        self::assertSame(
            'Content-Type: image/jpg; name=foo.jpg',
            $attachment->getPreparedHeaders()->toArray()[0]
        );
        self::assertSame(
            'Content-Disposition: attachment; name=foo.jpg; filename=foo.jpg',
            $attachment->getPreparedHeaders()->toArray()[2]
        );
    }
}
