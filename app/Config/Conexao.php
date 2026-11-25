<?php

class Conexao
{
    public static function conectar(): PDO
    {
        $host = getenv('DF_SYSTEM_DB_HOST') ?: '127.0.0.1';
        $porta = getenv('DF_SYSTEM_DB_PORT') ?: '3306';
        $banco = getenv('DF_SYSTEM_DB_NAME') ?: 'df_system';
        $usuario = getenv('DF_SYSTEM_DB_USER') ?: 'root';
        $senha = getenv('DF_SYSTEM_DB_PASSWORD');
        return new PDO(
            "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4",
            $usuario,
            $senha === false ? '' : $senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
