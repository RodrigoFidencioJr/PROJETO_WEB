<?php

class Database
{
    private string $host;
    private string $db;
    private string $user;
    private string $pass;

    public function __construct()
    {
        $this->loadEnv(dirname(__DIR__) . '/.env');

        $this->host = $this->requireEnv('DB_HOST');
        $this->db   = $this->requireEnv('DB_NAME');
        $this->user = $this->requireEnv('DB_USER');
        $this->pass = getenv('DB_PASS');

        if ($this->pass === false) {
            throw new RuntimeException('A variável de ambiente DB_PASS não foi definida.');
        }
    }

    private function loadEnv(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException(
                'Arquivo .env não encontrado. Copie .env.example para .env e configure o banco de dados.'
            );
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');

            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }

    private function requireEnv(string $name): string
    {
        $value = getenv($name);

        if ($value === false || $value === '') {
            throw new RuntimeException("A variável de ambiente {$name} não foi definida.");
        }

        return $value;
    }

    public function connect(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";

        return new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
