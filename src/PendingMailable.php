<?php

declare(strict_types=1);

namespace Zaphyr\Mail;

use Symfony\Component\Mime\Address;
use Zaphyr\Mail\Contracts\MailableInterface;
use Zaphyr\Mail\Contracts\MailerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class PendingMailable
{
    /**
     * @var Address|Address[]|string|string[]|null
     */
    protected Address|string|array|null $to = null;

    /**
     * @var Address|Address[]|string|string[]|null
     */
    protected Address|string|array|null $cc = null;

    /**
     * @var Address|Address[]|string|string[]|null
     */
    protected Address|string|array|null $bcc = null;

    /**
     * @param MailerInterface $mailer
     */
    public function __construct(protected MailerInterface $mailer)
    {
    }

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return $this
     */
    public function to(Address|string|array $addresses): static
    {
        $this->to = $addresses;

        return $this;
    }

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return $this
     */
    public function cc(Address|string|array $addresses): static
    {
        $this->cc = $addresses;

        return $this;
    }

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return $this
     */
    public function bcc(Address|string|array $addresses): static
    {
        $this->bcc = $addresses;

        return $this;
    }

    /**
     * @param MailableInterface $mailable
     *
     * @throws Exceptions\MailerException
     * @return void
     */
    public function send(MailableInterface $mailable): void
    {
        $mailable->to($this->to)->cc($this->cc)->bcc($this->bcc);

        $this->mailer->send($mailable);
    }
}
