<?php
class passwordManager
{
    private array $errors = [];

    /**
     * Validates password strength
     */
    public function ValidatePass(string $password)
    {
        if (strlen($password) < 8)
            $this->errors[] = "Password must be at least 8 characters.";
        if (!preg_match('/\d/', $password))
            $this->errors[] = "Include at least one number.";
        if (!preg_match('/[A-Z]/', $password))
            $this->errors[] = "Include at least one uppercase letter.";
        if (!preg_match('/[\W_]/', $password))
            $this->errors[] = "Include at least one special character.";
        return empty($this->errors);
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    public function getErrors(): array
    {
        return $this->errors;
    }
}
