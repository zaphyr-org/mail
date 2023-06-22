<?php

declare(strict_types=1);

namespace Zaphyr\Mail;

use DateTimeInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Zaphyr\Mail\Contracts\EmailBuilderInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EmailBuilder implements EmailBuilderInterface
{
    /**
     * @param SymfonyEmail $email
     */
    public function __construct(protected SymfonyEmail $email)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function from(Address|string $address): static
    {
        $this->email->from($address);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sender(Address|string $address): static
    {
        $this->email->sender($address);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function returnPath(Address|string $address): static
    {
        $this->email->returnPath($address);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function replyTo(Address|string|array $addresses): static
    {
        return $this->addAddresses('replyTo', $addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function to(Address|string|array $addresses, bool $override = false): static
    {
        if ($override) {
            is_array($addresses) ? $this->email->to(...$addresses) : $this->email->to($addresses);

            return $this;
        }

        return $this->addAddresses('to', $addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function cc(Address|string|array $addresses, bool $override = false): static
    {
        if ($override) {
            is_array($addresses) ? $this->email->cc(...$addresses) : $this->email->cc($addresses);

            return $this;
        }

        return $this->addAddresses('cc', $addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function bcc(Address|string|array $addresses, bool $override = false): static
    {
        if ($override) {
            is_array($addresses) ? $this->email->bcc(...$addresses) : $this->email->bcc($addresses);

            return $this;
        }

        return $this->addAddresses('bcc', $addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function subject(string $subject): static
    {
        $this->email->subject($subject);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function date(DateTimeInterface $date): static
    {
        $this->email->date($date);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function priority(int $priority): static
    {
        $this->email->priority($priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attachFile(string $path, string|null $name = null, string|null $contentType = null): static
    {
        $this->email->attachFromPath($path, $name, $contentType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attachData($body, string|null $name = null, string|null $contentType = null): static
    {
        $this->email->attach($body, $name, $contentType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSymfonyEmail(): SymfonyEmail
    {
        return $this->email;
    }

    /**
     * @param string                            $type
     * @param string|string[]|Address|Address[] $addresses
     *
     * @return $this
     */
    protected function addAddresses(string $type, Address|string|array $addresses): static
    {
        is_array($addresses)
            ? $this->email->{$type}(...$addresses)
            : $this->email->{'add' . ucfirst($type)}($addresses);

        return $this;
    }
}
