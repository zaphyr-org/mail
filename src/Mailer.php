<?php

declare(strict_types=1);

namespace Zaphyr\Mail;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Zaphyr\Mail\Contracts\EmailBuilderInterface;
use Zaphyr\Mail\Contracts\MailableInterface;
use Zaphyr\Mail\Contracts\MailerInterface;
use Zaphyr\Mail\Contracts\ViewInterface;
use Zaphyr\Mail\Exceptions\MailerException;
use Zaphyr\Mail\Utils\View;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Mailer implements MailerInterface
{
    /**
     * @const string
     */
    public const VIEW_HTML = 'html';

    /**
     * @const string
     */
    public const VIEW_TEXT = 'text';

    /**
     * @var Address|string|null
     */
    protected Address|string|null $from = null;

    /**
     * @var Address|string|null
     */
    protected Address|string|null $replyTo = null;

    /**
     * @var Address|string|null
     */
    protected Address|string|null $returnPath = null;

    /**
     * @var Address|Address[]|string|string[]|null
     */
    protected Address|string|array|null $to = null;

    /**
     * @param SymfonyMailerInterface $mailer
     * @param ViewInterface          $view
     * @param EmailBuilderInterface  $emailBuilder
     * @param string                 $charset
     */
    public function __construct(
        protected SymfonyMailerInterface $mailer,
        protected ViewInterface $view = new View(),
        protected EmailBuilderInterface $emailBuilder = new EmailBuilder(new SymfonyEmail()),
        protected string $charset = 'utf-8',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysFrom(Address|string $address): static
    {
        $this->from = $address;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysReplyTo(Address|string $address): static
    {
        $this->replyTo = $address;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysReturnPath(Address|string $address): static
    {
        $this->returnPath = $address;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysTo(Address|string|array $addresses): static
    {
        $this->to = $addresses;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function to(Address|string|array $addresses): PendingMailable
    {
        return (new PendingMailable($this))->to($addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function cc(Address|string|array $addresses): PendingMailable
    {
        return (new PendingMailable($this))->cc($addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function bcc(Address|string|array $addresses): PendingMailable
    {
        return (new PendingMailable($this))->bcc($addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function send(MailableInterface|array $view, array $data = [], ?callable $callback = null): void
    {
        if ($view instanceof MailableInterface) {
            $view->send($this);

            return;
        }

        $emailBuilder = $this->buildEmail();

        [$html, $text] = $this->parseView($view);

        if (is_callable($callback)) {
            $callback($emailBuilder);
        }

        $this->addContent($emailBuilder, $html, $text, $data);

        if ($this->to !== null) {
            $emailBuilder->to($this->to, true)->cc([], true)->bcc([], true);
        }

        try {
            $this->mailer->send($emailBuilder->getSymfonyEmail());
        } catch (TransportExceptionInterface $e) {
            throw new MailerException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailBuilder(): EmailBuilderInterface
    {
        return $this->emailBuilder;
    }

    protected function buildEmail(): EmailBuilderInterface
    {
        $emailBuilder = $this->emailBuilder;

        if ($this->from !== null) {
            $emailBuilder->from($this->from);
        }

        if ($this->replyTo !== null) {
            $emailBuilder->replyTo($this->replyTo);
        }

        if ($this->returnPath !== null) {
            $emailBuilder->returnPath($this->returnPath);
        }

        return $emailBuilder;
    }

    /**
     * @param array{html?: string|null, text?:string|null} $view
     *
     * @throws MailerException
     * @return array<int, string|null>
     */
    protected function parseView(array $view): array
    {
        if (!empty($view[self::VIEW_HTML]) || !empty($view[self::VIEW_TEXT])) {
            return [
                $view[self::VIEW_HTML] ?? null,
                $view[self::VIEW_TEXT] ?? null,
            ];
        }

        throw new MailerException(
            'Invalid email view configuration. Must be an array containing "html" and/or "text" element'
        );
    }

    /**
     * @param EmailBuilderInterface $emailBuilder
     * @param string|null           $html
     * @param string|null           $text
     * @param array<string, mixed>  $data
     *
     * @throws MailerException
     * @return void
     */
    protected function addContent(
        EmailBuilderInterface $emailBuilder,
        ?string $html,
        ?string $text,
        array $data
    ): void {
        if ($html) {
            $emailBuilder->getSymfonyEmail()->html($this->view->render($html, $data), $this->charset);
        }

        if ($text) {
            $emailBuilder->getSymfonyEmail()->text($this->view->render($text, $data), $this->charset);
        }
    }
}
