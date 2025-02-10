<?php

declare(strict_types=1);

namespace Zaphyr\Mail\Contracts;

use Symfony\Component\Mime\Address;
use Zaphyr\Mail\Exceptions\MailerException;
use Zaphyr\Mail\PendingMailable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface MailerInterface
{
    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function alwaysFrom(Address|string $address): static;

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function alwaysReplyTo(Address|string $address): static;

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function alwaysReturnPath(Address|string $address): static;

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return $this
     */
    public function alwaysTo(Address|string|array $addresses): static;

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return PendingMailable
     */
    public function to(Address|string|array $addresses): PendingMailable;

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return PendingMailable
     */
    public function cc(Address|string|array $addresses): PendingMailable;

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return PendingMailable
     */
    public function bcc(Address|string|array $addresses): PendingMailable;

    /**
     * @param MailableInterface|array{html?:string|null, text?:string|null} $view
     * @param array<string, mixed>                                          $data
     * @param callable|null                                                 $callback
     *
     * @throws MailerException if the email could not be sent.
     * @return void
     */
    public function send(MailableInterface|array $view, array $data = [], ?callable $callback = null): void;

    /**
     * @return EmailBuilderInterface
     */
    public function getEmailBuilder(): EmailBuilderInterface;
}
