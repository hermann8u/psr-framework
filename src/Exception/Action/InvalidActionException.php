<?php

declare(strict_types=1);

namespace App\Exception\Action;

use App\Exception\ExceptionInterface;

class InvalidActionException extends \InvalidArgumentException implements ExceptionInterface
{
    /** @var object */
    protected $action;

    /** @var string */
    protected $reasons;

    public function __construct(object $action, string $reasons = '', $code = 0, \Throwable $previous = null)
    {
        $message = sprintf(
            'The action "%s" is invalid. %s',
            get_class($action),
            $reasons
        );

        parent::__construct($message, $code, $previous);

        $this->action = $action;
        $this->reasons = $reasons;
    }

    public function getAction(): object
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getReasons(): string
    {
        return $this->reasons;
    }
}
