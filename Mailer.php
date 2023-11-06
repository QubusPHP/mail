<?php

/**
 * Qubus\Mail
 *
 * @link       https://github.com/QubusPHP/mail
 * @copyright  2023
 * @author     Joshua Parker <joshua@joshuaparker.dev>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Qubus\Mail;

use Qubus\Exception\Exception;

interface Mailer
{
    /**
     * Save message as eml file.
     *
     * @return bool True if saved successfully, false otherwise.
     * @throws Exception
     */
    public function save(): bool;
}
