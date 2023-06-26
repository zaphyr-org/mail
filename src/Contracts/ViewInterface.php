<?php

declare(strict_types=1);

namespace Zaphyr\Mail\Contracts;

use Zaphyr\Mail\Exceptions\MailerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ViewInterface
{
    /**
     * @param string              $path
     * @param array<string,mixed> $data
     *
     * @throws MailerException
     * @return string
     */
    public function render(string $path, array $data): string;
}
