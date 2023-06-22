<?php

declare(strict_types=1);

namespace Zaphyr\Mail;

use DateTimeInterface;
use Symfony\Component\Mime\Address;
use Zaphyr\Mail\Contracts\EmailBuilderInterface;
use Zaphyr\Mail\Contracts\MailableInterface;
use Zaphyr\Mail\Contracts\MailerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractMailable implements MailableInterface
{
    /**
     * @var Address[]|string[]
     */
    protected array $to = [];

    /**
     * @var Address[]|string[]
     */
    protected array $cc = [];

    /**
     * @var Address[]|string[]
     */
    protected array $bcc = [];

    /**
     * @var Address|string|null
     */
    protected Address|string|null $from = null;

    /**
     * @var Address|string|null
     */
    protected Address|string|null $sender = null;

    /**
     * @var Address|string|null
     */
    protected Address|string|null $returnPath = null;

    /**
     * @var Address[]|string[]
     */
    protected array $replyTo = [];

    /**
     * @var string|null
     */
    protected string|null $subject = null;

    /**
     * @var DateTimeInterface|null
     */
    protected DateTimeInterface|null $date = null;

    /**
     * @var int|null
     */
    protected int|null $priority = null;

    /**
     * @var array<array{path: string, name: string|null, contentType: string|null}>
     */
    protected array $attachmentFiles = [];

    /**
     * @var array<array{body: string|resource, name: string|null, contentType: string|null}>
     */
    protected array $attachmentData = [];

    /**
     * @var string|null
     */
    protected string|null $html = null;

    /**
     * @var string|null
     */
    protected string|null $text = null;

    /**
     * @var array{html?: string|null, text?: string|null}
     */
    protected array $view = [];

    /**
     * @var array<string, mixed>
     */
    protected array $viewData = [];

    /**
     * @return void
     */
    abstract public function build(): void;

    /**
     * {@inheritdoc}
     */
    public function to(Address|string|array|null $addresses): static
    {
        return $this->setAddresses($addresses, 'to');
    }

    /**
     * {@inheritdoc}
     */
    public function cc(Address|string|array|null $addresses): static
    {
        return $this->setAddresses($addresses, 'cc');
    }

    /**
     * {@inheritdoc}
     */
    public function bcc(Address|string|array|null $addresses): static
    {
        return $this->setAddresses($addresses, 'bcc');
    }

    /**
     * {@inheritdoc}
     */
    public function send(MailerInterface $mailer): void
    {
        $this->build();

        $mailer->send($this->buildView(), $this->viewData, function (EmailBuilderInterface $emailBuilder) {
            $this->buildEmailData($emailBuilder)->buildAttachments($emailBuilder);
        });
    }

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function from(Address|string $address): static
    {
        $this->from = $address;

        return $this;
    }

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function sender(Address|string $address): static
    {
        $this->sender = $address;

        return $this;
    }

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function returnPath(Address|string $address): static
    {
        $this->returnPath = $address;

        return $this;
    }

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return $this
     */
    public function replyTo(Address|string|array $addresses): static
    {
        return $this->setAddresses($addresses, 'replyTo');
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param DateTimeInterface $date
     *
     * @return $this
     */
    public function date(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function priority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @param string      $path
     * @param string|null $name
     * @param string|null $contentType
     *
     * @return $this
     */
    public function attachFile(string $path, string|null $name = null, string|null $contentType = null): static
    {
        $this->attachmentFiles[] = compact('path', 'name', 'contentType');

        return $this;
    }

    /**
     * @param string|resource $body
     * @param string|null     $name
     * @param string|null     $contentType
     *
     * @return $this
     */
    public function attachData($body, string|null $name = null, string|null $contentType = null): static
    {
        $this->attachmentData[] = compact('body', 'name', 'contentType');

        return $this;
    }

    /**
     * @param string               $path
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function html(string $path, array $data = []): static
    {
        $this->html = $path;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * @param string               $path
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function text(string $path, array $data = []): static
    {
        $this->text = $path;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * @param array{html?: string, text?: string} $view
     * @param array<string, mixed>                $data
     *
     * @return $this
     */
    public function view(array $view, array $data = []): static
    {
        $this->view = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * @param Address|Address[]|string|string[]|null $addresses
     * @param string                                 $property
     *
     * @return $this
     */
    protected function setAddresses(Address|string|array|null $addresses, string $property): static
    {
        if (empty($addresses)) {
            return $this;
        }

        if (!is_array($addresses)) {
            $addresses = [$addresses];
        }

        $this->{$property} = $addresses;

        return $this;
    }

    /**
     * @return array{html?: string|null, text?: string|null}
     */
    protected function buildView(): array
    {
        $view = [];

        if ($this->html !== null) {
            $view['html'] = $this->html;
        }

        if ($this->text !== null) {
            $view['text'] = $this->text;
        }

        return [...$view, ...$this->view];
    }

    /**
     * @param EmailBuilderInterface $emailBuilder
     *
     * @return $this
     */
    protected function buildEmailData(EmailBuilderInterface $emailBuilder): static
    {
        foreach (['from', 'sender', 'returnPath', 'subject', 'date', 'priority'] as $type) {
            if ($this->{$type} !== null) {
                $emailBuilder->{$type}($this->{$type});
            }
        }

        foreach (['replyTo', 'to', 'cc', 'bcc'] as $type) {
            foreach ($this->{$type} as $recipient) {
                $emailBuilder->{$type}($recipient);
            }
        }

        return $this;
    }

    /**
     * @param EmailBuilderInterface $emailBuilder
     *
     * @return $this
     */
    protected function buildAttachments(EmailBuilderInterface $emailBuilder): static
    {
        foreach ($this->attachmentFiles as $attachment) {
            $emailBuilder->attachFile($attachment['path'], $attachment['name'], $attachment['contentType']);
        }

        foreach ($this->attachmentData as $attachment) {
            $emailBuilder->attachData($attachment['body'], $attachment['name'], $attachment['contentType']);
        }

        return $this;
    }
}
