<?php

declare(strict_types=1);

namespace Zaphyr\Mail\Utils;

use Zaphyr\Mail\Contracts\ViewInterface;
use Zaphyr\Mail\Exceptions\MailerException;
use Zaphyr\Utils\Exceptions\UtilsException;
use Zaphyr\Utils\Template;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class View implements ViewInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(string $path, array $data): string
    {
        try {
            return Template::render($path, $data);
        } catch (UtilsException $e) {
            throw new MailerException($e->getMessage());
        }
    }
}
