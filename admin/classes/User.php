<?php

class User
{
    private $data = [];


    // --- Setters ---
    public function setFirstName($val)
    {
        $this->data['first_name'] = $val;
    }
    public function setLastName($val)
    {
        $this->data['last_name'] = $val;
    }
    public function setUsername($val)
    {
        $this->data['username'] = $val;
    }
    public function setPassword($val)
    {
        $this->data['password'] = $val;
    }
    public function setRole($val)
    {
        $this->data['role'] = $val;
    }
    // Setters (Data goes IN)



    // --- Getters (The parts you requested) ---
    public function getUsername(): ?string
    {
        return $this->data['username'] ?? null;
    }
    public function getPassword(): ?string
    {
        return $this->data['password'] ?? null;
    }
    public function getFirstName(): ?string
    {
        return $this->data['first_name'] ?? null;
    }
    public function getLastName(): ?string
    {
        return $this->data['last_name'] ?? null;
    }
    public function getRole()
    {
        return $this->data['role'] ?? null;
    }
    public function getAllData(): array
    {
        return $this->data;
    }

}

