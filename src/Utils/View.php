<?php

declare(strict_types=1);

namespace Zaphyr\Mail\Utils;

use Zaphyr\Mail\Contracts\ViewInterface;
use Zaphyr\Mail\Exceptions\MailerException;

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
        $template = is_file($path) ? file_get_contents($path) : false;

        if ($template === false) {
            throw new MailerException('Could not find email template "' . $path . '"');
        }

        if (!empty($data)) {
            foreach ($data as $name => $value) {
                $template = str_replace('%' . $name . '%', $value, $template);
            }
        }

        return $template;
    }
}
