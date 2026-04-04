<?php
/**
 * Location: leccionario-digital/app/Core/Result.php
 */

/**
 * Result class - Standard response object for services
 */
class Result
{
    // ********** Properties **********
    private bool $success;
    private string $message;
    private $data;
    private array $errors;

    // ********** Constructor **********
    public function __construct(bool $success, string $message, $data = null, array $errors = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
    }

    // ********** Factory Methods **********
    public static function success(string $message, $data = null): self
    {
        return new self(true, $message, $data);
    }

    public static function error(string $message, array $errors = []): self
    {
        return new self(false, $message, null, $errors);
    }

    // ********** Getters **********
    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    // ********** Converters **********
    public function toArray(): array
    {
        $result = [
            'success' => $this->success,
            'message' => $this->message
        ];

        if ($this->data !== null) {
            $result['data'] = $this->data;
        }

        if (!empty($this->errors)) {
            $result['errors'] = $this->errors;
        }

        return $result;
    }
}
