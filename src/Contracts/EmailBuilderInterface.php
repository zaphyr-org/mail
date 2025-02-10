<?php

declare(strict_types=1);

namespace Zaphyr\Mail\Contracts;

use DateTimeInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface EmailBuilderInterface
{
    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function from(Address|string $address): static;

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function sender(Address|string $address): static;

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function returnPath(Address|string $address): static;

    /**
     * @param Address|Address[]|string|string[] $addresses
     *
     * @return $this
     */
    public function replyTo(Address|string|array $addresses): static;

    /**
     * @param Address|Address[]|string|string[] $addresses
     * @param bool                              $override
     *
     * @return $this
     */
    public function to(Address|string|array $addresses, bool $override = false): static;

    /**
     * @param Address|Address[]|string|string[] $addresses
     * @param bool                              $override
     *
     * @return $this
     */
    public function cc(Address|string|array $addresses, bool $override = false): static;

    /**
     * @param Address|Address[]|string|string[] $addresses
     * @param bool                              $override
     *
     * @return $this
     */
    public function bcc(Address|string|array $addresses, bool $override = false): static;

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject): static;

    /**
     * @param DateTimeInterface $date
     *
     * @return $this
     */
    public function date(DateTimeInterface $date): static;

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function priority(int $priority): static;

    /**
     * @param string      $path
     * @param string|null $name
     * @param string|null $contentType
     *
     * @return $this
     */
    public function attachFile(string $path, ?string $name = null, ?string $contentType = null): static;

    /**
     * @param string|resource $body
     * @param string|null     $name
     * @param string|null     $contentType
     *
     * @return $this
     */
    public function attachData(
        $body,
        ?string $name = null,
        ?string $contentType = null
    ): static;

    /**
     * @return SymfonyEmail
     */
    public function getSymfonyEmail(): SymfonyEmail;
}
