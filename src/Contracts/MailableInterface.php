<?php

declare(strict_types=1);

namespace Zaphyr\Mail\Contracts;

use Symfony\Component\Mime\Address;
use Zaphyr\Mail\Exceptions\MailerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface MailableInterface
{
    /**
     * @param Address|Address[]|string|string[]|null $addresses
     *
     * @return $this
     */
    public function to(Address|string|array|null $addresses): static;

    /**
     * @param Address|Address[]|string|string[]|null $addresses
     *
     * @return $this
     */
    public function cc(Address|string|array|null $addresses): static;

    /**
     * @param Address|Address[]|string|string[]|null $addresses
     *
     * @return $this
     */
    public function bcc(Address|string|array|null $addresses): static;

    /**
     * @param MailerInterface $mailer
     *
     * @throws MailerException if the email could not be sent.
     * @return void
     */
    public function send(MailerInterface $mailer): void;
}
