<?php

declare(strict_types=1);

namespace Zaphyr\MailTests;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mime\Address;
use Zaphyr\Mail\AbstractMailable;
use Zaphyr\Mail\Contracts\EmailBuilderInterface;
use Zaphyr\Mail\Exceptions\MailerException;
use Zaphyr\Mail\Mailer;

class MailerTest extends TestCase
{
    /**
     * @var SymfonyMailer&MockObject
     */
    protected SymfonyMailer&MockObject $symfonyMailerMock;

    /**
     * @var Mailer
     */
    protected Mailer $mailer;

    public function setUp(): void
    {
        $this->symfonyMailerMock = $this->createMock(SymfonyMailer::class);
        $this->mailer = new Mailer($this->symfonyMailerMock);
    }

    public function tearDown(): void
    {
        unset($this->symfonyMailerMock, $this->mailer);
    }

    /* -------------------------------------------------
     * SEND WITH CLOSURE
     * -------------------------------------------------
     */

    public function testSendWithClosure(): void
    {
        $this->symfonyMailerMock->expects(self::once())->method('send');
        $bccAddress = new Address('bcc@example.com');
        $date = new DateTimeImmutable('now');
        $this->mailer->send(
            view: [
                Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html',
                Mailer::VIEW_TEXT => __DIR__ . '/TestAssets/resources/templates/welcome.txt',
            ],
            callback: function (EmailBuilderInterface $email) use ($bccAddress, $date) {
                $email
                    ->from('from@example.com')
                    ->sender('sender@example.com')
                    ->returnPath('returnPath@example.com')
                    ->replyTo('replyTo@example.com')
                    ->to('to@example.com')
                    ->cc('cc@example.com')
                    ->bcc($bccAddress)
                    ->subject('Welcome')
                    ->date($date)
                    ->priority(2)
                    ->attachFile(__DIR__ . '/TestAssets/resources/templates/welcome.txt')
                    ->attachData(__DIR__ . '/TestAssets/resources/templates/welcome.txt');
            }
        );

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringContainsString('Content-Type: text/plain; charset=utf-8', $body);
        self::assertStringContainsString('Content-Type: text/html; charset=utf-8', $body);
        self::assertStringContainsString('From: from@example.com', $body);
        self::assertStringContainsString('Sender: sender@example.com', $body);
        self::assertStringContainsString('Return-Path: <returnPath@example.com>', $body);
        self::assertStringContainsString('Reply-To: replyTo@example.com', $body);
        self::assertStringContainsString('To: to@example.com', $body);
        self::assertStringContainsString('Cc: cc@example.com', $body);
        self::assertEquals($bccAddress, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getBcc()[0]);
        self::assertStringContainsString('Subject: Welcome', $body);
        self::assertEquals($date, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getDate());
        self::assertStringContainsString('X-Priority: 2 (High)', $body);
        self::assertStringContainsString('Content-Disposition: attachment; name=welcome.txt;', $body);
        self::assertStringContainsString(
            'welcome.txt',
            $this->mailer->getEmailBuilder()->getSymfonyEmail()->getAttachments()[1]->getBody()
        );
    }

    public function testSendHtmlViewWithClosure(): void
    {
        $this->symfonyMailerMock->expects(self::once())->method('send');

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) {
                $email
                    ->from('from@example.com')
                    ->to('to@example.com')
                    ->subject('Welcome');
            }
        );

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringNotContainsString('Content-Type: text/plain; charset=utf-8', $body);
        self::assertStringContainsString('Content-Type: text/html; charset=utf-8', $body);
    }

    public function testSendTextViewWithClosure(): void
    {
        $this->symfonyMailerMock->expects(self::once())->method('send');

        $this->mailer->send(
            view: [Mailer::VIEW_TEXT => __DIR__ . '/TestAssets/resources/templates/welcome.txt'],
            callback: function (EmailBuilderInterface $email) {
                $email
                    ->from('from@example.com')
                    ->to('to@example.com')
                    ->subject('Welcome');
            }
        );

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringContainsString('Content-Type: text/plain; charset=utf-8', $body);
        self::assertStringNotContainsString('Content-Type: text/html; charset=utf-8', $body);
    }

    public function testSendThrowsExceptionOnInvalidViewConfiguration(): void
    {
        $this->expectException(MailerException::class);
        $this->expectExceptionMessage(
            'Invalid email view configuration. Must be an array containing "html" and/or "text" element'
        );

        $this->mailer->send(['/TestAssets/resources/templates/welcome.txt']);
    }

    public function testSendThrowsExceptionWhenViewFileCouldNotBeFound(): void
    {
        $this->expectException(MailerException::class);
        $this->expectExceptionMessage('Template file "not-found.html"');

        $this->mailer->send([Mailer::VIEW_HTML => 'not-found.html']);
    }

    public function testSendThrowsExceptionWhenEmailCouldNotSend(): void
    {
        $this->symfonyMailerMock->expects(self::once())->method('send')->willThrowException(
            new TransportException('An error occured')
        );

        $this->expectException(MailerException::class);

        $this->mailer->send([Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html']);
    }

    /* -------------------------------------------------
     * SEND WITH MAILABLE
     * -------------------------------------------------
     */

    public function testSendWithMailable(): void
    {
        $bccAddress = new Address('bcc@example.com');
        $dateTime = new DateTimeImmutable('now');

        $mailable = new class ($bccAddress, $dateTime) extends AbstractMailable {
            public function __construct(protected Address $bccAddress, protected DateTimeImmutable $dateTime)
            {
            }

            public function build(): void
            {
                $this
                    ->view([
                        Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html',
                        Mailer::VIEW_TEXT => __DIR__ . '/TestAssets/resources/templates/welcome.txt',
                    ], ['name' => 'John Doe'])
                    ->from('from@example.com')
                    ->sender('sender@example.com')
                    ->returnPath('returnPath@example.com')
                    ->replyTo('replyTo@example.com')
                    ->to('to@example.com')
                    ->cc('cc@example.com')
                    ->bcc($this->bccAddress)
                    ->subject('Welcome')
                    ->date($this->dateTime)
                    ->priority(2)
                    ->attachFile(__DIR__ . '/TestAssets/resources/templates/welcome.txt')
                    ->attachData(__DIR__ . '/TestAssets/resources/templates/welcome.txt');
            }
        };

        $this->mailer->send($mailable);

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringContainsString('Content-Type: text/plain; charset=utf-8', $body);
        self::assertStringContainsString('Content-Type: text/html; charset=utf-8', $body);
        self::assertStringContainsString('From: from@example.com', $body);
        self::assertStringContainsString('Sender: sender@example.com', $body);
        self::assertStringContainsString('Return-Path: <returnPath@example.com>', $body);
        self::assertStringContainsString('Reply-To: replyTo@example.com', $body);
        self::assertStringContainsString('To: to@example.com', $body);
        self::assertStringContainsString('Cc: cc@example.com', $body);
        self::assertEquals($bccAddress, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getBcc()[0]);
        self::assertStringContainsString('Subject: Welcome', $body);
        self::assertEquals($dateTime, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getDate());
        self::assertStringContainsString('X-Priority: 2 (High)', $body);
        self::assertStringContainsString('Content-Disposition: attachment; name=welcome.txt;', $body);
        self::assertStringContainsString(
            'welcome.txt',
            $this->mailer->getEmailBuilder()->getSymfonyEmail()->getAttachments()[1]->getBody()
        );
    }

    public function testSendWithHtmlMailable(): void
    {
        $mailable = new class extends AbstractMailable {
            public function build(): void
            {
                $this
                    ->from('jonh@doe.com')
                    ->html(__DIR__ . '/TestAssets/resources/templates/welcome.html', ['name' => 'John Doe']);
            }
        };

        $this->mailer->to('to@example.com')->send($mailable);

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringContainsString('To: to@example.com', $body);
        self::assertStringContainsString('Content-Type: text/html; charset=utf-8', $body);
        self::assertStringContainsString('Welcome John Doe', $body);
    }

    public function testSendWithTextMailable(): void
    {
        $mailable = new class extends AbstractMailable {
            public function build(): void
            {
                $this
                    ->from('from@example.com')
                    ->text(__DIR__ . '/TestAssets/resources/templates/welcome.txt', ['name' => 'John Doe']);
            }
        };

        $this->mailer->to('to@example.com')->send($mailable);

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringContainsString('To: to@example.com', $body);
        self::assertStringContainsString('Content-Type: text/plain; charset=utf-8', $body);
        self::assertStringContainsString('Welcome John Doe', $body);
    }

    public function testSendWithViewMailable(): void
    {
        $mailable = new class extends AbstractMailable {
            public function build(): void
            {
                $this
                    ->from('from@example.com')
                    ->view([
                        Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html',
                        Mailer::VIEW_TEXT => __DIR__ . '/TestAssets/resources/templates/welcome.txt',
                    ], ['name' => 'John Doe']);
            }
        };

        $this->mailer->to('to@example.com')->send($mailable);

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringContainsString('To: to@example.com', $body);
        self::assertStringContainsString('Content-Type: text/plain; charset=utf-8', $body);
        self::assertStringContainsString('Content-Type: text/html; charset=utf-8', $body);
        self::assertStringContainsString('Welcome John Doe', $body);
    }

    public function testMailableWithPendingMailable(): void
    {
        $mailable = new class extends AbstractMailable {
            public function build(): void
            {
                $this
                    ->from('from@example.com')
                    ->view([
                        Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html',
                        Mailer::VIEW_TEXT => __DIR__ . '/TestAssets/resources/templates/welcome.txt',
                    ], ['name' => 'John Doe']);
            }
        };

        $bccAddress = new Address('bcc@example.com');

        $this->mailer->to('to@example.com')->cc('cc@example.com')->bcc($bccAddress)->send($mailable);

        $body = $this->mailer->getEmailBuilder()->getSymfonyEmail()->toString();

        self::assertStringContainsString('From: from@example.com', $body);
        self::assertStringContainsString('To: to@example.com', $body);
        self::assertStringContainsString('Cc: cc@example.com', $body);
        self::assertEquals($bccAddress, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getBcc()[0]);
    }

    /* -------------------------------------------------
     * ALWAYS FROM
     * -------------------------------------------------
     */

    public function testAlwaysFrom(): void
    {
        $address = new Address('alwaysFrom@example.com');

        $this->mailer->alwaysFrom($address);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) {
                $email->to('to@example.com');
            }
        );

        self::assertEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getFrom()[0]);
    }

    public function testAlwaysFromCanBeOverwritten(): void
    {
        $address = new Address('alwaysFrom@example.com');
        $overwriteAddress = new Address('overwriteAlwaysFrom@example.com');

        $this->mailer->alwaysFrom($address);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) use ($overwriteAddress) {
                $email->from($overwriteAddress)->to('to@example.com');
            }
        );

        self::assertNotEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getFrom()[0]);
        self::assertEquals($overwriteAddress, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getFrom()[0]);
    }

    /* -------------------------------------------------
     * ALWAYS REPLY TO
     * -------------------------------------------------
     */

    public function testAlwaysReplyTo(): void
    {
        $address = new Address('alwaysReplyTo@example.com');

        $this->mailer->alwaysReplyTo($address);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) {
                $email->to('to@example.com');
            }
        );

        self::assertEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getReplyTo()[0]);
    }

    public function testAlwaysReplyToCanBeOverwritten(): void
    {
        $address = new Address('alwaysReplyTo@example.com');
        $overwriteAddress = new Address('overwriteAlwaysReplayTo@example.com');

        $this->mailer->alwaysReplyTo($overwriteAddress);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) use ($overwriteAddress) {
                $email->replyTo($overwriteAddress)->to('overwriteAlwaysReplayTo@example.com');
            }
        );

        self::assertNotEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getReplyTo()[0]);
        self::assertEquals($overwriteAddress, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getReplyTo()[0]);
    }

    /* -------------------------------------------------
     * ALWAYS RETURN PATH
     * -------------------------------------------------
     */

    public function testAlwaysReturnPath(): void
    {
        $address = new Address('alwaysTo@example.com');

        $this->mailer->alwaysReturnPath($address);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) {
                $email->to('to@example.com');
            }
        );

        self::assertEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getReturnPath());
    }

    public function testAlwaysReturnPathCanBeOverwritten(): void
    {
        $address = new Address('returnPath@example.com');
        $overwriteAddress = new Address('overwriteReturnPath@example.com');

        $this->mailer->alwaysReturnPath($overwriteAddress);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) use ($overwriteAddress) {
                $email->replyTo($overwriteAddress)->to('to@example.com');
            }
        );

        self::assertNotEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getReturnPath());
        self::assertEquals($overwriteAddress, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getReturnPath());
    }

    /* -------------------------------------------------
     * ALWAYS TO
     * -------------------------------------------------
     */

    public function testAlwaysTo(): void
    {
        $address = new Address('to@example.com');

        $this->mailer->alwaysTo($address);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $emailBuilder) {
                $emailBuilder->from('sender@email.com')
                    ->to('to@example.com')
                    ->cc('cc@example.com')
                    ->bcc('bcc@example.com');
            }
        );

        $symfonyEmail = $this->mailer->getEmailBuilder()->getSymfonyEmail();
        $body = $symfonyEmail->toString();

        self::assertEquals([$address], $symfonyEmail->getTo());
        self::assertStringContainsString('To: to@example.com', $body);
        self::assertStringNotContainsString('CC: cc@example.com', $body);
        self::assertStringNotContainsString('Bcc: bcc@example.com', $body);
    }

    public function testAlwaysToWithMultipleAddresses(): void
    {
        $this->mailer->alwaysTo(['alwaysTo1@example.com', new Address('alwaysTo2@example.com')]);

        $this->mailer->send([Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html']);

        self::assertEquals(
            [new Address('alwaysTo1@example.com'), new Address('alwaysTo2@example.com')],
            $this->mailer->getEmailBuilder()->getSymfonyEmail()->getTo()
        );
    }

    public function testAlwaysToCanBeOverwritten(): void
    {
        $address = new Address('to@example.com');
        $overwriteAddress = new Address('overwriteTo@example.com');

        $this->mailer->alwaysTo($overwriteAddress);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) use ($overwriteAddress) {
                $email->to($overwriteAddress);
            }
        );

        self::assertNotEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getTo()[0]);
        self::assertEquals($overwriteAddress, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getTo()[0]);
    }

    public function testAlwaysToResetsCcAndBccAddress(): void
    {
        $address = new Address('alwaysTo@example.com');

        $this->mailer->alwaysTo($address);

        $this->mailer->send(
            view: [Mailer::VIEW_HTML => __DIR__ . '/TestAssets/resources/templates/welcome.html'],
            callback: function (EmailBuilderInterface $email) {
                $email->cc('cc@example.com')->bcc('bcc@example.com');
            }
        );

        self::assertEquals($address, $this->mailer->getEmailBuilder()->getSymfonyEmail()->getTo()[0]);
        self::assertEquals([], $this->mailer->getEmailBuilder()->getSymfonyEmail()->getCc());
        self::assertEquals([], $this->mailer->getEmailBuilder()->getSymfonyEmail()->getBcc());
    }
}
